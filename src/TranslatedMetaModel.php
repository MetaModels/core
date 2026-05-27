<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\ISimple;
use MetaModels\Attribute\ITranslated;
use MetaModels\Helper\LocaleUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This defines a translated MetaModel.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TranslatedMetaModel extends MetaModel implements ITranslatedMetaModel
{
    /**
     * The current active language.
     *
     * @var string
     */
    private string $activeLanguage = '';

    /**
     * The fallback language.
     *
     * @var string
     */
    private string $mainLanguage = '';

    /**
     * The locale territory support.
     *
     * @var bool
     */
    private bool $hasTerritorySupport;

    /**
     * Instantiate a MetaModel.
     *
     * @param array                    $arrData    The information array, for information on the available
     *                                             columns, refer to documentation of table tl_metamodel.
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param Connection               $connection The database connection.
     *
     * @throws \RuntimeException When no language has been marked as main language.
     */
    public function __construct($arrData, EventDispatcherInterface $dispatcher, Connection $connection)
    {
        parent::__construct($arrData, $dispatcher, $connection);

        foreach ($this->arrData['languages'] as $languageCode => $languageData) {
            if ($languageData['isfallback']) {
                $this->mainLanguage = $languageCode;
            }
        }

        if (null === $this->mainLanguage) {
            throw new \RuntimeException('No language marked as fallback.');
        }
        // Mark fallback language as active language.
        $this->activeLanguage = $this->mainLanguage;

        $this->hasTerritorySupport = (bool) ($this->arrData['localeterritorysupport'] ?? false);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getLanguages(): array
    {
        return \array_keys((array) $this->arrData['languages']);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getMainLanguage(): string
    {
        return $this->mainLanguage;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getLanguage(): string
    {
        return $this->activeLanguage;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function selectLanguage(string $activeLanguage): string
    {
        $previousLanguage = $this->getLanguage();

        if (!$this->hasTerritorySupport) {
            $activeLanguage = \substr($activeLanguage, 0, 2);
        }

        if (!\in_array($activeLanguage, $this->getLanguages(), true)) {
            $activeLanguage = $this->getMainLanguage();
        }

        $this->activeLanguage = $activeLanguage;

        return $previousLanguage;
    }

    /**
     * {@inheritDoc}
     *
     * This is only overridden for BC reasons.
     * To be removed in MetaModels 3.0.
     *
     * @deprecated Since 2.2 to be private in 3.0.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    #[\Override]
    protected function fetchTranslatedAttributeValues(ITranslated $attribute, $ids)
    {
        // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
        $originalLanguage       = LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE'] ?? 'en');
        $GLOBALS['TL_LANGUAGE'] = LocaleUtil::formatAsLanguageTag($this->getLanguage());

        try {
            $attributeData = $attribute->getTranslatedDataFor($ids, $this->getLanguage());
            // Second round, fetch missing data from main language.
            if ([] !== $missing = \array_values(\array_diff($ids, \array_keys($attributeData)))) {
                $attributeData += $attribute->getTranslatedDataFor($missing, $this->getMainLanguage());
            }

            return $attributeData;
        } finally {
            // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
            $GLOBALS['TL_LANGUAGE'] = LocaleUtil::formatAsLanguageTag($originalLanguage);
        }
    }

    /**
     * Update the variants with the value if needed.
     *
     * @param IItem  $item           The item to save.
     * @param string $activeLanguage The language the values are in.
     * @param int[]  $allIds         The ids of all variants.
     * @param bool   $baseAttributes If also the base attributes get updated as well.
     *
     * @return void
     */
    #[\Override]
    protected function updateVariants($item, $activeLanguage, $allIds, $baseAttributes = false): void
    {
        $mainLanguage = $this->getMainLanguage();
        if ($mainLanguage === $activeLanguage) {
            parent::updateVariants($item, $activeLanguage, $allIds, $baseAttributes);
            return;
        }

        $fallbackItem = $this->loadFallbackItem($item, $mainLanguage);

        foreach ($this->getAttributes() as $attributeName => $attribute) {
            if ($this->shouldSkipAttributeUpdate($item, $attribute, $baseAttributes)) {
                continue;
            }

            $idList = ($item->isVariantBase() && !($attribute->get('isvariant')))
                ? $allIds
                : [$item->get('id')];

            if ($this->hasSameFallbackValue($item, $attribute, $attributeName, $fallbackItem)) {
                $this->clearAttribute($attribute, $idList, $activeLanguage);
                continue;
            }

            $this->saveAttribute($attribute, $idList, $item->get($attributeName), $activeLanguage);
        }
    }

    /**
     * Load the item in the main (fallback) language for comparison.
     */
    private function loadFallbackItem(IItem $item, string $mainLanguage): ?IItem
    {
        $currentLanguage = $this->getLanguage();
        $this->selectLanguage($mainLanguage);
        try {
            return $this->getItemsWithId([$item->get('id')], $item->getSetAttributes())->getItem();
        } finally {
            $this->selectLanguage($currentLanguage);
        }
    }

    /**
     * Check whether the attribute value matches the fallback item value.
     * Returns false for simple attributes or when no fallback item is available.
     */
    private function hasSameFallbackValue(
        IItem $item,
        IAttribute $attribute,
        string $attributeName,
        ?IItem $fallbackItem
    ): bool {
        if ($attribute instanceof ISimple || null === $fallbackItem) {
            return false;
        }
        return $attribute->valueToWidget($item->get($attributeName))
            === $attribute->valueToWidget($fallbackItem->get($attributeName));
    }
}
