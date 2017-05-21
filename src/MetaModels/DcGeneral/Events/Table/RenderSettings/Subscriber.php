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
 * @author     Martin Treml <github@r2pi.net>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use MenAtWork\MultiColumnWizard\Event\GetOptionsEvent;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\Dca\Helper;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSettings;

/**
 * Handles event operations on tl_metamodel_rendersettings.
 */
class Subscriber extends BaseSubscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $serviceContainer = $this->getServiceContainer();
        $this
            ->addListener(
                GetBreadcrumbEvent::NAME,
                function (GetBreadcrumbEvent $event) use ($serviceContainer) {
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')) {
                        return;
                    }
                    $subscriber = new BreadCrumbRenderSettings($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'modelToLabel')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeJumpToValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeJumpToValue')
            )
            ->addListener(
                BuildWidgetEvent::NAME,
                array($this, 'buildJumpToWidget')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getTemplateOptions')
            )
            ->addListener(
                GetOptionsEvent::NAME,
                array($this, 'getCssFilesOptions')
            )
            ->addListener(
                GetOptionsEvent::NAME,
                array($this, 'getJsFilesOptions')
            );
    }

    /**
     * Draw the render setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')) {
            return;
        }

        if ($event->getModel()->getProperty('isdefault')) {
            $event->setLabel(
                $event->getLabel() .
                ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>'
            );
        }
    }

    /**
     * Translates the values of the jumpTo entries into the real array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeJumpToValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
        || ($event->getProperty() !== 'jumpTo')) {
            return;
        }

        $propInfo = $event
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($event->getProperty());
        $value    = deserialize($event->getValue(), true);

        $extra = $propInfo->getExtra();

        $newValues    = array();
        $arrLanguages = $extra['columnFields']['langcode']['options'];

        foreach (array_keys($arrLanguages) as $key) {
            $newValue  = '';
            $intFilter = 0;
            if ($value) {
                foreach ($value as $arr) {
                    if (!is_array($arr)) {
                        break;
                    }

                    // Set the new value and exit the loop.
                    if (array_search($key, $arr) !== false) {
                        $newValue  = '{{link_url::'.$arr['value'].'}}';
                        $intFilter = $arr['filter'];
                        break;
                    }
                }
            }

            // Build the new array.
            $newValues[] = array(
                'langcode' => $key,
                'value'    => $newValue,
                'filter'   => $intFilter
            );
        }

        $event->setValue($newValues);
    }

    /**
     * Translates the values of the jumpTo entries into the internal array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeJumpToValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
            || ($event->getProperty() !== 'jumpTo')) {
            return;
        }

        $value = deserialize($event->getValue(), true);

        foreach ($value as $k => $v) {
            $value[$k]['value'] = str_replace(
                array('{{link_url::', '}}'),
                array('',''),
                $v['value']
            );
        }

        $event->setValue(serialize($value));
    }

    /**
     * Retrieve the model filters for the MCW.
     *
     * @param ModelInterface $model The model containing the currently edited render setting.
     *
     * @return array
     */
    protected function getFilterSettings(ModelInterface $model)
    {
        $objFilters = $this
            ->getDatabase()
            ->prepare('SELECT id, name FROM tl_metamodel_filter WHERE pid = ?')
            ->execute($model->getProperty('pid'));

        $result = array();
        while ($objFilters->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $result[$objFilters->id] = $objFilters->name;
        }

        return $result;
    }

    /**
     * Provide options for template selection.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function buildJumpToWidget(BuildWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
            || ($event->getProperty()->getName() !== 'jumpTo')) {
            return;
        }

        $model      = $event->getModel();
        $metaModel  = $this->getMetaModelById($model->getProperty('pid'));
        $translator = $event->getEnvironment()->getTranslator();

        $extra = $event->getProperty()->getExtra();

        if ($metaModel->isTranslated()) {
            $arrLanguages = array();
            foreach ((array) $metaModel->getAvailableLanguages() as $strLangCode) {
                $arrLanguages[$strLangCode] = $translator->translate('LNG.'. $strLangCode, 'languages');
            }
            asort($arrLanguages);

            $extra['minCount'] = count($arrLanguages);
            $extra['maxCount'] = count($arrLanguages);

            $extra['columnFields']['langcode']['options'] = $arrLanguages;
        } else {
            $extra['minCount'] = 1;
            $extra['maxCount'] = 1;

            $extra['columnFields']['langcode']['options'] = array
            (
                'xx' => $GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_allLanguages']
            );
        }

        $extra['columnFields']['filter']['options'] = self::getFilterSettings($model);

        $event->getProperty()->setExtra($extra);
    }

    /**
     * Provide options for template selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getTemplateOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
            || ($event->getPropertyName() !== 'template')) {
            return;
        }

        $list = new TemplateList();
        $list->setServiceContainer($this->getServiceContainer());
        $event->setOptions($list->getTemplatesForBase('metamodel_'));
    }

    /**
     * Provide options for additional css files.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getCssFilesOptions(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
            || ($event->getPropertyName() !== 'additionalCss')
            || ($event->getSubPropertyName() !== 'file')) {
            return;
        }

        $options = Helper::searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], '.css');

        $event->setOptions($options);
    }

    /**
     * Provide options for additional javascript files.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getJsFilesOptions(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
            || ($event->getPropertyName() !== 'additionalJs')
            || ($event->getSubPropertyName() !== 'file')) {
            return;
        }

        $options = Helper::searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], '.js');

        $event->setOptions($options);
    }
}
