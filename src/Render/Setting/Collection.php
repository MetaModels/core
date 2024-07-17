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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render\Setting;

use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base implementation for render settings.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection implements ICollection
{
    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private FilterUrlBuilder $filterUrlBuilder;

    /**
     * The base information for this render settings object.
     *
     * @var array
     */
    protected $arrBase = [];

    /**
     * The sub settings for all attributes.
     *
     * @var array
     */
    protected $arrSettings = [];

    /**
     * The jump to information buffered in this setting.
     *
     * @var array
     */
    protected $jumpToCache = [];

    /**
     * Create a new instance.
     *
     * @param IMetaModel               $metaModel        The MetaModel instance.
     * @param array                    $arrInformation   The array that holds all base information for the new instance.
     * @param EventDispatcherInterface $dispatcher       The event dispatcher.
     * @param IFilterSettingFactory    $filterFactory    The filter setting factory.
     * @param FilterUrlBuilder|null    $filterUrlBuilder The filter URL builder.
     */
    public function __construct(
        IMetaModel $metaModel,
        array $arrInformation,
        EventDispatcherInterface $dispatcher,
        IFilterSettingFactory $filterFactory,
        FilterUrlBuilder $filterUrlBuilder = null
    ) {
        $this->metaModel     = $metaModel;
        $this->dispatcher    = $dispatcher;
        $this->filterFactory = $filterFactory;

        if (null === $filterUrlBuilder) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the "FilterUrlBuilder" as 5th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
            assert($filterUrlBuilder instanceof FilterUrlBuilder);
        }
        $this->filterUrlBuilder = $filterUrlBuilder;

        foreach ($arrInformation as $strKey => $varValue) {
            $this->set($strKey, StringUtil::deserialize($varValue));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($strName)
    {
        return $this->arrBase[$strName] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($strName, $varSetting)
    {
        $this->arrBase[$strName] = $varSetting;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetting($strAttributeName)
    {
        return ($this->arrSettings[$strAttributeName] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function setSetting($strAttributeName, $objSetting)
    {
        if ($objSetting) {
            $this->arrSettings[$strAttributeName] = $objSetting->setParent($this);
        } else {
            unset($this->arrSettings[$strAttributeName]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingNames()
    {
        return \array_keys($this->arrSettings);
    }

    /**
     * Retrieve the jump to label.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @psalm-suppress PossiblyNullArrayOffset
     */
    private function getJumpToLabel()
    {
        $tableName = $this->metaModel->getTableName();
        if (
            null !== ($label = ($GLOBALS['TL_LANG']['MSC'][$tableName][$this->get('id')]['details'] ??
            ($GLOBALS['TL_LANG']['MSC'][$tableName]['details'] ??
            ($GLOBALS['TL_LANG']['MSC']['details'] ?? null))))
        ) {
            return $label;
        }

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        return $translator->trans('details', [], $tableName);
    }

    /**
     * Retrieve the details for the page with the given id.
     *
     * @param string $pageId The id of the page to retrieve the details for.
     *
     * @return array
     */
    private function getPageDetails(string $pageId): array
    {
        if (empty($pageId)) {
            return [];
        }

        $event = new GetPageDetailsEvent((int) $pageId);
        $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GET_PAGE_DETAILS);

        return $event->getPageDetails();
    }

    /**
     * Determine the page id and other details.
     *
     * @return array
     */
    private function determineJumpToInformation(): array
    {
        // Get the right jumpto.
        $translated       = false;
        $desiredLanguage  = null;
        $fallbackLanguage = null;

        /** @psalm-suppress DeprecatedMethod */
        if ($this->metaModel instanceof ITranslatedMetaModel) {
            $translated       = true;
            $desiredLanguage  = $this->metaModel->getLanguage();
            $fallbackLanguage = $this->metaModel->getMainLanguage();
            /** @psalm-suppress DeprecatedMethod */
        } elseif ($this->metaModel->isTranslated()) {
            // @coverageIgnoreStart
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Translated "\MetaModel\IMetamodel" instances are deprecated since MetaModels 2.2 ' .
                'and to be removed in 3.0. The MetaModel must implement "\MetaModels\ITranslatedMetaModel".',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $translated       = true;
            /** @psalm-suppress DeprecatedMethod */
            $desiredLanguage = $this->metaModel->getActiveLanguage();
            /** @psalm-suppress DeprecatedMethod */
            $fallbackLanguage = $this->metaModel->getFallbackLanguage();
            // @coverageIgnoreEnd
        }

        $cacheKey = ($desiredLanguage ?? '') . '.' . ($fallbackLanguage ?? '');
        if (!isset($this->jumpToCache[$cacheKey])) {
            $this->jumpToCache[$cacheKey] = $this->lookupJumpTo($translated, $desiredLanguage, $fallbackLanguage);
        }

        return $this->jumpToCache[$cacheKey];
    }

    /**
     * Look up the jump to url.
     *
     * @param bool        $translated Flag if the MetaModel is translated.
     * @param string|null $desired    The desired language.
     * @param string|null $fallback   The fallback language.
     *
     * @return array
     */
    private function lookupJumpTo(bool $translated, string $desired = null, string $fallback = null): array
    {
        $jumpToPageId    = '';
        $filterSettingId = '';
        $referenceType   = UrlGeneratorInterface::ABSOLUTE_PATH;
        foreach ((array) $this->get('jumpTo') as $jumpTo) {
            $langCode = $jumpTo['langcode'] ?? null;
            // If either desired language or fallback, keep the result.
            if (!$translated || ($langCode === $desired) || ($langCode === $fallback)) {
                $jumpToPageId    = $jumpTo['value'] ?? '';
                $filterSettingId = (string) ($jumpTo['filter'] ?? '');
                $referenceType = (int) ($jumpTo['type'] ?? UrlGeneratorInterface::ABSOLUTE_PATH);
                // If the desired language, break.
                // Otherwise, try to get the desired one until all have been evaluated.
                if (!$translated || ($desired === $jumpTo['langcode'])) {
                    break;
                }
            }
        }

        $pageDetails   = $this->getPageDetails($jumpToPageId);
        $filterSetting = $filterSettingId
            ? $this->filterFactory->createCollection($filterSettingId)
            : null;

        return [
            'page'          => $jumpToPageId,
            'pageDetails'   => $pageDetails,
            'filter'        => $filterSettingId,
            'filterSetting' => $filterSetting,
            'referenceType' => $referenceType,
            // Mask out the "all languages" language key (See #687).
            'language'      => $pageDetails['language'] ?? '',
            'label'         => $this->getJumpToLabel()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildJumpToUrlFor(IItem $item /**, int $referenceType */)
    {
        $information = $this->determineJumpToInformation();
        if (empty($information['pageDetails'])) {
            return [];
        }

        $result        = $information;
        $parameterList = [];

        $filterUrl = new FilterUrl($information['pageDetails']);
        if (!empty($information['language'])) {
            $filterUrl->setPageValue('language', $information['language']);
        }

        if (!empty($information['filterSetting'])) {
            /** @var \MetaModels\Filter\Setting\ICollection $filterSetting */
            $filterSetting = $information['filterSetting'];
            $parameterList = $filterSetting->generateFilterUrlFrom($item, $this);

            foreach ($parameterList as $strKey => $strValue) {
                // Sadly the filter values are currently encoded due to legacy reasons.
                // For MetaModels 3, they should be passed around decoded everywhere.
                $filterUrl->setSlug($strKey, \rawurldecode($strValue))->setGet($strKey, '');
            }
        }

        $result['params'] = $parameterList;
        $result['deep']   = !empty($filterUrl->getSlugParameters());

        $result['url'] = $this->filterUrlBuilder->generate(
            $filterUrl,
            $information['referenceType']
                ?? ((1 < func_num_args()) ? (int) func_get_arg(1) : UrlGeneratorInterface::ABSOLUTE_PATH)
        );

        return $result;
    }
}
