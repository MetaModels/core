<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Doctrine\DBAL\Connection;
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
    public function getLanguages(): array
    {
        return \array_keys((array) $this->arrData['languages']);
    }

    /**
     * {@inheritDoc}
     */
    public function getMainLanguage(): string
    {
        return $this->mainLanguage;
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage(): string
    {
        return $this->activeLanguage;
    }

    /**
     * {@inheritdoc}
     */
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
}
