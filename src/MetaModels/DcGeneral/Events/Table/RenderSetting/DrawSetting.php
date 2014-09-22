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

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\Factory;

/**
 * Handle event to draw a render setting.
 *
 * @package MetaModels\DcGeneral\Events\Table\RenderSettings
 */
class DrawSetting
{
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
    public static function modelToLabel(ModelToLabelEvent $event)
    {
        $model        = $event->getModel();
        $objSetting   = \Database::getInstance()
            ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
            ->execute($model->getProperty('pid'));
        $objMetaModel = Factory::byId($objSetting->pid);

        $objAttribute = $objMetaModel->getAttributeById($model->getProperty('attr_id'));

        if ($objAttribute) {
            $type  = $objAttribute->get('type');
            $image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
            if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
                $image = 'system/modules/metamodels/assets/images/icons/fields.png';
            }
            $name    = $objAttribute->getName();
            $colName = $objAttribute->getColName();
        } else {
            $translator = $event->getEnvironment()->getTranslator();
            $image      = 'system/modules/metamodels/assets/images/icons/fields.png';
            $name       = $translator->translate('error_unknown_id', 'error_unknown_attribute');
            $colName    = $translator->translate('error_unknown_column', 'error_unknown_attribute');
            $type       = $translator->translate(
                'error_unknown_id',
                'tl_metamodel_rendersettings',
                array($model->getProperty('attr_id'))
            );
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventPropagator()->propagate(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );

        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong>
                </div>')
            ->setArgs(array(
                $model->getProperty('enabled') ? 'published' : 'unpublished',
                $colName,
                $type,
                $imageEvent->getHtml(),
                $name
            ));
    }
}
