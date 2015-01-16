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

namespace MetaModels\Attribute;

use MetaModels\Attribute\Events\CollectMetaModelAttributeInformationEvent;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This is the implementation of the Field factory to query instances of fields.
 *
 * Usually this is only used internally by {@link MetaModels\Factory}
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 *
 * @deprecated Use AttributeFactory instead.
 */
class Factory extends AttributeFactory implements IFactory
{
    /**
     * Retrieve the default factory from the default container.
     *
     * @return IFactory
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getDefaultFactory()
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getAttributeFactory();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Use an instance of the factory and method createAttribute().
     */
    public static function createFromArray($arrData)
    {
        return self::getDefaultFactory()->createAttribute($arrData, \MetaModels\Factory::byId($arrData['pid']));
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Use an instance of the factory and method createAttribute().
     */
    public static function createFromDB($objRow)
    {
        return self::createFromArray($objRow->row());
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Use an instance of the factory and method createAttribute().
     */
    public static function getAttributesFor($objMetaModel)
    {
        return self::getDefaultFactory()->createAttributesForMetaModel($objMetaModel);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Will not be in available anymore - if you need this, file a ticket.
     */
    public static function getAttributeTypes($blnSupportTranslated = false, $blnSupportVariants = false)
    {
        $flags = self::FLAG_ALL_UNTRANSLATED;
        if ($blnSupportTranslated) {
            $flags |= self::FLAG_INCLUDE_TRANSLATED;
        }

        return self::getDefaultFactory()->getTypeNames($flags);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Will not be in available anymore - if you need this, file a ticket.
     */
    public static function isValidAttributeType($strFieldType)
    {
        trigger_error(
            'WARNING: isValidAttributeType is deprecated Will not be in available anymore - ' .
            'if you need this, file a ticket.',
            E_USER_WARNING
        );

        return (bool) self::getDefaultFactory()->getTypeFactory($strFieldType);
    }
}
