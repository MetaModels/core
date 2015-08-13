<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Render\Setting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use MetaModels\IItem;
use MetaModels\IMetaModel;

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
     * @param IMetaModel $metaModel      The MetaModel instance.
     *
     * @param array      $arrInformation The array that holds all base information for the new instance.
     */
    public function __construct(IMetaModel $metaModel, $arrInformation)
    {
        $this->metaModel = $metaModel;

        foreach ($arrInformation as $strKey => $varValue) {
            $this->set($strKey, deserialize($varValue));
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
        return isset($this->arrSettings[$strAttributeName]) ? $this->arrSettings[$strAttributeName] : null;
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

        if (isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->get('id')]['details'])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->get('id')]['details'];
        } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName]['details'])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName]['details'];
        }

        return $GLOBALS['TL_LANG']['MSC']['details'];
    }

    /**
     * Retrieve the details for the page with the given id.
     *
     * @param string $pageId The id of the page to retrieve the details for.
     *
     * @return array
     */
    private function getPageDetails($pageId)
    {
        if (empty($pageId)) {
            return array();
        }
        $event = new GetPageDetailsEvent($pageId);
        $this
            ->metaModel
            ->getServiceContainer()
            ->getEventDispatcher()
            ->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);

        return $event->getPageDetails();
    }

    /**
     * Determine the page id and other details.
     *
     * @return array
     */
    private function determineJumpToInformation()
    {
        // Get the right jumpto.
        $translated       = $this->metaModel->isTranslated();
        $desiredLanguage  = $this->metaModel->getActiveLanguage();
        $fallbackLanguage = $this->metaModel->getFallbackLanguage();
        $jumpToPageId     = '';
        $filterSettingId  = '';

        if (!isset($this->jumpToCache[$desiredLanguage . '.' . $fallbackLanguage])) {
            $serviceContainer = $this->metaModel->getServiceContainer();

            foreach ((array) $this->get('jumpTo') as $jumpTo) {
                $langCode = $jumpTo['langcode'];
                // If either desired language or fallback, keep the result.
                if (!$translated || ($langCode == $desiredLanguage) || ($langCode == $fallbackLanguage)) {
                    $jumpToPageId    = $jumpTo['value'];
                    $filterSettingId = $jumpTo['filter'];
                    // If the desired language, break.
                    // Otherwise try to get the desired one until all have been evaluated.
                    if ($desiredLanguage == $jumpTo['langcode']) {
                        break;
                    }
                }
            }

            $pageDetails   = $this->getPageDetails($jumpToPageId);
            $filterSetting = $filterSettingId
                ? $serviceContainer->getFilterFactory()->createCollection($filterSettingId)
                : null;

            $this->jumpToCache[$desiredLanguage . '.' . $fallbackLanguage] = array(
                'page'          => $jumpToPageId,
                'pageDetails'   => $pageDetails,
                'filter'        => $filterSettingId,
                'filterSetting' => $filterSetting,
                // Mask out the "all languages" language key (See #687).
                'language'      => $pageDetails['language'],
                'label'         => $this->getJumpToLabel()
            );
        }

        return $this->jumpToCache[$desiredLanguage . '.' . $fallbackLanguage];
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
        $parameters    = '';
        $parameterList = array();

        if (!empty($information['filterSetting'])) {
            /** @var \MetaModels\Filter\Setting\ICollection $filterSetting */
            $filterSetting = $information['filterSetting'];
            $parameterList = $filterSetting->generateFilterUrlFrom($item, $this);

            foreach ($parameterList as $strKey => $strValue) {
                if ($strKey == 'auto_item') {
                    $parameters = '/' . $strValue . $parameters;
                } else {
                    $parameters .= sprintf('/%s/%s', $strKey, $strValue);
                }
            }
        }

        $result['params'] = $parameterList;
        $result['deep']   = (strlen($parameters) > 0);

        $event = new GenerateFrontendUrlEvent(
            $information['pageDetails'],
            $parameters,
            $information['language']
        );

        $this
            ->metaModel
            ->getServiceContainer()
            ->getEventDispatcher()
            ->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);
        $result['url'] = $event->getUrl();

        return $result;
    }
}
