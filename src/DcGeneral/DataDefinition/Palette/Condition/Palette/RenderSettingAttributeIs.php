<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\AbstractWeightAwarePaletteCondition;

/**
 * Condition for the default palette.
 */
class RenderSettingAttributeIs extends AbstractWeightAwarePaletteCondition
{
    /**
     * The expected property value.
     *
     * @var mixed
     */
    protected $attributeType;

    /**
     * Buffer the attribute types to ease lookup.
     *
     * @var array
     */
    protected static $attributeTypes = array();

    /**
     * Create a new instance.
     *
     * @param string $attributeType The attribute type name.
     *
     * @param int    $weight        The weight of this condition to apply.
     */
    public function __construct($attributeType, $weight = 1)
    {
        $this->attributeType = $attributeType;
        $this->setWeight($weight);
    }

    /**
     * Set the attribute type name.
     *
     * @param string $attributeType The attribute type name.
     *
     * @return RenderSettingAttributeIs
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * Retrieve the attribute type name.
     *
     * @return mixed
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * Retrieve the type name from an attribute.
     *
     * @param int $value The id of an attribute.
     *
     * @return string
     */
    public function getTypeOfAttribute($value)
    {
        if (!isset(self::$attributeTypes[$value])) {
            self::$attributeTypes[$value] = $this->getServiceContainer()->getDatabase()
                ->prepare('SELECT type FROM tl_metamodel_attribute WHERE id=?')
                ->limit(1)
                ->execute($value)
                ->type;
        }
        return self::$attributeTypes[$value];
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
    {
        if ($input && $input->hasPropertyValue('attr_id')) {
            $value = $input->getPropertyValue('attr_id');
        } elseif ($model) {
            $value = $model->getProperty('attr_id');
        } else {
            return false;
        }

        return ($this->getTypeOfAttribute($value) == $this->getAttributeType()) ? $this->getWeight() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }
}
