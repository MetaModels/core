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
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\IMetaModelsServiceContainer;

/**
 * This is the implementation of the Field factory to query instances of fields.
 *
 * Usually this is only used internally by {@link MetaModels\Factory}
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
