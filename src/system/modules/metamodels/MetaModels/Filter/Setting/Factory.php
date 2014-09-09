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

namespace MetaModels\Filter\Setting;

/**
 * This is the IMetaModelFilter factory interface.
 *
 * To create a IMetaModelFilter instance, call {@link MetaModelFilter::byId()}
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Factory implements IFactory
{
    /**
     * Keeps track of all filter settings instances to save DB lookup queries.
     *
     * @var ICollection[]
     */
    protected static $arrInstances = array();

    /**
     * Create a IMetaModelFilter instance from the id.
     *
     * @param int $intId The id of the IMetaModelFilter.
     *
     * @return ICollection the instance of the IMetaModelFilterSettings or null if not found.
     */
    public static function byId($intId)
    {
        if (empty(self::$arrInstances[$intId]))
        {
            $objDB = \Database::getInstance();

            $arrSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
                ->execute($intId)
                ->row();

            if (!empty($arrSettings))
            {
                $objSetting = new Collection($arrSettings);
                $objSetting->collectRules();
            } else {
                $objSetting = new Collection(array());
            }
            self::$arrInstances[$intId] = $objSetting;
        } else {
            $objSetting = self::$arrInstances[$intId];
        }

        return $objSetting;
    }
}

