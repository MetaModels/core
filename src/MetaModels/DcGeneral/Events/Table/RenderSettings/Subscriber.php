<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
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
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')) {
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
                PostPersistModelEvent::NAME,
                array($this, 'handleUpdate')
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
                GetPropertyOptionsEvent::NAME,
                array($this, 'getCssFilesOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')) {
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
     * Handle the update of a render setting.
     *
     * This resets the default flags for all other render settings when becoming the default.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function handleUpdate(PostPersistModelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

        $new = $event->getModel();

        if (!$new->getProperty('isdefault')) {
            return;
        }

        $this
            ->getDatabase()
            ->prepare('UPDATE tl_metamodel_rendersettings
                    SET isdefault = \'\'
                    WHERE pid=?
                        AND id<>?
                        AND isdefault=1')
            ->execute(
                $new->getProperty('pid'),
                $new->getId()
            );
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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
        || ($event->getProperty() !== 'jumpTo')) {
            return;
        }

        $propInfo = $event
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($event->getProperty());
        $value    = deserialize($event->getValue(), true);

        if (!$value) {
            return;
        }

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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
            || ($event->getProperty() !== 'jumpTo')) {
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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
            || ($event->getPropertyName() !== 'template')) {
            return;
        }

        $options = Helper::getTemplatesForBase('metamodel_');

        $event->setOptions($options);
    }

    /**
     * Provide options for additional css files.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getCssFilesOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
            || ($event->getPropertyName() !== 'additionalCss')) {
            return;
        }

        $options = Helper::searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], '.css');

        $event->setOptions($options);
    }

    /**
     * Provide options for additional javascript files.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getJsFilesOptions(GetPropertyOptionsEvent $event)
    {
        $options = Helper::searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], '.js');

        $event->setOptions($options);
    }
}
