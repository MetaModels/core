<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
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

/**
 * Base implementation for render settings.
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
    private $dispatcher;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

    /**
     * The base information for this render settings object.
     *
     * @var array
     */
    protected $arrBase = array();

    /**
     * The sub settings for all attributes.
     *
     * @var array
     */
    protected $arrSettings = array();

    /**
     * The jump to information buffered in this setting.
     *
     * @var array
     */
    protected $jumpToCache;

    /**
     * Create a new instance.
     *
     * @param IMetaModel               $metaModel        The MetaModel instance.
     * @param array                    $arrInformation   The array that holds all base information for the new instance.
     * @param EventDispatcherInterface $dispatcher       The event dispatcher.
     * @param IFilterSettingFactory    $filterFactory    The filter setting factory.
     * @param FilterUrlBuilder         $filterUrlBuilder The filter URL builder.
     */
    public function __construct(
        IMetaModel $metaModel,
        $arrInformation,
        EventDispatcherInterface $dispatcher,
        IFilterSettingFactory $filterFactory,
        FilterUrlBuilder $filterUrlBuilder = null
    ) {
        $this->metaModel     = $metaModel;
        $this->dispatcher    = $dispatcher;
        $this->filterFactory = $filterFactory;
        if (null === $this->dispatcher) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the event dispatcher as 3rd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->dispatcher = System::getContainer()->get('event_dispatcher');
        }
        if (null === $this->filterFactory) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the filter setting factory as 4th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->filterFactory = System::getContainer()->get('metamodels.filter_setting_factory');
        }
        if (null === $filterUrlBuilder) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the "FilterUrlBuilder" as 5th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
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
        return $this->arrBase[$strName];
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
        return array_keys($this->arrSettings);
    }

    /**
     * Retrieve the jump to label.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getJumpToLabel()
    {
        $tableName = $this->metaModel->getTableName();

        return ($GLOBALS['TL_LANG']['MSC'][$tableName][$this->get('id')]['details'] ??
               ($GLOBALS['TL_LANG']['MSC'][$tableName]['details'] ??
               $GLOBALS['TL_LANG']['MSC']['details']));
    }

    /**
     * Retrieve the details for the page with the given id.
     *
     * @param string $pageId The id of the page to retrieve the details for.
     *
     * @return array
     */
    private function getPageDetails($pageId): array
    {
        if (empty($pageId)) {
            return [];
        }

        $event = new GetPageDetailsEvent($pageId);
        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);

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
        if ($this->metaModel instanceof ITranslatedMetaModel) {
            $translated       = true;
            $desiredLanguage  = $this->metaModel->getLanguage();
            $fallbackLanguage = $this->metaModel->getMainLanguage();
        } elseif ($this->metaModel->isTranslated(false)) {
            // @coverageIgnoreStart
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Translated "\MetaModel\IMetamodel" instances are deprecated since MetaModels 2.1 ' .
                'and to be removed in 3.0. The MetaModel must implement "\MetaModels\ITranslatedMetaModel".',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $translated       = true;
            $desiredLanguage  = $this->metaModel->getActiveLanguage();
            $fallbackLanguage = $this->metaModel->getFallbackLanguage();
            // @coverageIgnoreEnd
        }

        $cacheKey = $desiredLanguage . '.' . $fallbackLanguage;
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
        foreach ((array) $this->get('jumpTo') as $jumpTo) {
            $langCode = $jumpTo['langcode'];
            // If either desired language or fallback, keep the result.
            if (!$translated || ($langCode === $desired) || ($langCode === $fallback)) {
                $jumpToPageId    = $jumpTo['value'];
                $filterSettingId = $jumpTo['filter'];
                // If the desired language, break.
                // Otherwise try to get the desired one until all have been evaluated.
                if (!$translated || ($desired === $jumpTo['langcode'])) {
                    break;
                }
            }
        }

        $pageDetails   = $this->getPageDetails($jumpToPageId);
        $filterSetting = $filterSettingId
            ? $this->getFilterFactory()->createCollection($filterSettingId)
            : null;

        return [
            'page'          => $jumpToPageId,
            'pageDetails'   => $pageDetails,
            'filter'        => $filterSettingId,
            'filterSetting' => $filterSetting,
            // Mask out the "all languages" language key (See #687).
            'language'      => $pageDetails['language'],
            'label'         => $this->getJumpToLabel()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildJumpToUrlFor(IItem $item)
    {
        $information = $this->determineJumpToInformation();
        if (empty($information['pageDetails'])) {
            return array();
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
                $filterUrl->setSlug($strKey, rawurldecode($strValue));
            }
        }

        $result['params'] = $parameterList;
        $result['deep']   = !empty($filterUrl->getSlugParameters());

        $result['url'] = $this->filterUrlBuilder->generate($filterUrl);

        return $result;
    }
}
