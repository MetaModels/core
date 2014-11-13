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
     * The default factory instance.
     *
     * @var IFactory
     */
    protected static $defaultFactory;

    /**
     * Inline create an instance of this factory.
     *
     * @return IFactory
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private static function createDefaultFactory()
    {
        return new static($GLOBALS['container']['event-dispatcher']);
    }

    /**
     * Inline create an instance of this factory.
     *
     * @return IFactory
     *
     * @deprecated You should not use this method it is part of the backward compatibility layer.
     */
    public static function getDefaultFactory()
    {
        if (!self::$defaultFactory) {
            self::$defaultFactory = self::createDefaultFactory();
        }

        return self::$defaultFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function collectAttributeInformation(IMetaModel $metaModel)
    {
        $event = new CollectMetaModelAttributeInformationEvent($metaModel);

        $this->getEventDispatcher()->dispatch($event::NAME, $event);

        return $event->getAttributeInformation();
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributesForMetaModel($metaModel)
    {
        $attributes = array();
        foreach ($this->collectAttributeInformation($metaModel) as $information) {
            $attribute = $this->createAttribute($information, $metaModel);
            if ($attribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
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
