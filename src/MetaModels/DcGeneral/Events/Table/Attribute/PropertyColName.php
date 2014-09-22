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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\Helper\TableManipulation;

/**
 * Handle events for property "colname".
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class PropertyColName extends AttributeBase
{
    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When the column name is illegal or duplicate.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        $oldColumnName = $event->getModel()->getProperty($event->getProperty());
        $columnName    = $event->getValue();
        $metaModel     = self::getMetaModelFromModel($event->getModel());

        if ((!$columnName) || $oldColumnName !== $columnName) {
            TableManipulation::checkColumnDoesNotExist($metaModel->getTableName(), $columnName);

            $colNames = array_keys($metaModel->getAttributes());
            if (in_array($columnName, $colNames)) {
                throw new \RuntimeException(
                    sprintf(
                        $GLOBALS['TL_LANG']['ERR']['columnExists'],
                        $columnName,
                        $metaModel->getTableName()
                    )
                );
            }
        }
    }
}
