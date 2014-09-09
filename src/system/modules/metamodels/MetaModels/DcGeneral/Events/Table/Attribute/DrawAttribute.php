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

namespace MetaModels\DcGeneral\Events\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\Attribute\Factory;

/**
 * Class DrawAttribute
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class DrawAttribute
{
    /**
     * Draw the attribute in the backend listing.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public static function modelToLabel(ModelToLabelEvent $event)
    {
        // FIXME: Add language files for the error msg.

        $model     = $event->getModel();
        $type      = $model->getProperty('type');
        $image     = '<img src="' . $GLOBALS['METAMODELS']['attributes'][$type]['image'] . '" />';
        $attribute = Factory::createFromArray($model->getPropertiesAsArray());

        if (!$attribute)
        {
            $event
                ->setLabel(
                    '<div class="field_heading cte_type"><strong>Unknown attribute!</strong> <em>[%s]</em></div>
                    <div class="field_type block">
                        <strong>Extension missing? The attribute type "%s" is not installed.</strong><br />
                    </div>'
                )
                ->setArgs(array(
                    $type,
                    $type
                ));
            return;
        }

        $colName        = $attribute->getColName();
        $name           = $attribute->getName();
        $arrDescription = deserialize($attribute->get('description'));
        if (is_array($arrDescription))
        {
            $description = $arrDescription[$attribute->getMetaModel()->getActiveLanguage()];
            if (!$description)
            {
                $description = $arrDescription[$attribute->getMetaModel()->getFallbackLanguage()];
            }
        } else {
            $description = $attribute->getName();
        }

        $event
            ->setLabel(
                '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong> - %s
                </div>'
            )
            ->setArgs(array(
                $colName,
                $type,
                $image,
                $name,
                $description
            ));
    }
}
