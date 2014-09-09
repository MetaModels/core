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

use MetaModels\Factory as MetaModelFactory;

/**
 * This is the implementation of the Field factory to query instances of fields.
 * Usually this is only used internally by {@link MetaModel}
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Factory implements IFactory
{
    /**
     * All attribute instances for all MetaModels that are created via this factory.
     *
     * @var IAttribute[]
     */
    protected static $arrAttributes = array();

    /**
     * Determines the correct class from a field type name.
     *
     * @param string $strFieldType The field type of which the class shall be fetched from.
     *
     * @return string the class name which handles the field type or NULL if no class could be found.
     */
    protected static function getAttributeTypeClass($strFieldType)
    {
        if (isset($GLOBALS['METAMODELS']['attributes'][$strFieldType]['class']))
        {
            return $GLOBALS['METAMODELS']['attributes'][$strFieldType]['class'];
        }

        return null;
    }

    /**
     * Determines the correct factory from a field type name.
     *
     * @param string $strFieldType The field type of which the factory class shall be fetched from.
     *
     * @return string The factory class name which handles instantiation of the field type or NULL if no class could
     *                be found.
     */
    protected static function getAttributeTypeFactory($strFieldType)
    {
        if (isset($GLOBALS['METAMODELS']['attributes'][$strFieldType]['factory']))
        {
            return $GLOBALS['METAMODELS']['attributes'][$strFieldType]['factory'];
        }

        return null;
    }

    /**
     * Create a MetaModelAttribute instance with the given information.
     *
     * @param array $arrData The meta information for the attribute.
     *
     * @return IAttribute|null the created instance or null if unable to construct.
     */
    protected static function createInstance($arrData)
    {
        $strFactoryName = self::getAttributeTypeFactory($arrData['type']);

        $objAttribute = null;
        if ($strFactoryName)
        {
            $objAttribute = call_user_func_array(array($strFactoryName, 'createInstance'), array($arrData));
        }
        else
        {
            $strClassName = self::getAttributeTypeClass($arrData['type']);
            if ($strClassName)
            {
                $objMetaModel = MetaModelFactory::byId($arrData['pid']);
                $objAttribute = new $strClassName($objMetaModel, $arrData);
            }
        }
        return $objAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromArray($arrData)
    {
        return self::createInstance($arrData);
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromDB($objRow)
    {
        return self::createInstance($objRow->row());
    }

    /**
     * {@inheritdoc}
     */
    public static function getAttributesFor($objMetaModel)
    {
        $objDB         = \Database::getInstance();
        $objAttributes = $objDB->prepare('SELECT * FROM tl_metamodel_attribute WHERE pid=?')
            ->execute($objMetaModel->get('id'));

        $arrAttributes = array();
        while ($objAttributes->next())
        {
            if (isset(self::$arrAttributes[$objAttributes->id]))
            {
                $arrAttributes[] = self::$arrAttributes[$objAttributes->id];
            }
            else
            {
                $objAttribute = self::createFromDB($objAttributes);
                if ($objAttribute)
                {
                    $arrAttributes[] = $objAttribute;

                    self::$arrAttributes[$objAttributes->id] = $objAttribute;
                }
            }
        }
        return $arrAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAttributeTypes($blnSupportTranslated = false, $blnSupportVariants = false)
    {
        if ($blnSupportTranslated)
        {
            return array_keys($GLOBALS['METAMODELS']['attributes']);
        }
        $arrRet = array();
        foreach ($GLOBALS['METAMODELS']['attributes'] as $strKey => $arrInformation)
        {
            $arrInterfaces = class_implements($arrInformation['class'], true);
            // Skip translated field types if translation is not supported.
            if ((!$blnSupportTranslated && in_array('MetaModels\Attribute\ITranslated', $arrInterfaces)))
            {
                continue;
            }

            // TODO: will we really ever have some interface like this?
            // Skip variant fields if variants are not supported.
            if ((!$blnSupportVariants && in_array('MetaModels\Attribute\IVariant', $arrInterfaces)))
            {
                continue;
            }
            $arrRet[] = $strKey;
        }
        return $arrRet;
    }

    /**
     * {@inheritdoc}
     */
    public static function isValidAttributeType($strFieldType)
    {
        return array_key_exists($strFieldType, $GLOBALS['METAMODELS']['attributes']);
    }
}

