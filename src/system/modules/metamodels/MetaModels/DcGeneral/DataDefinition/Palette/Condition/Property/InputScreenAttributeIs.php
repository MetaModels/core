<?php
/**
 * PHP version 5
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Condition for the default palette.
 */
class InputScreenAttributeIs implements PropertyConditionInterface
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
	 */
	public function __construct($attributeType)
	{
		$this->attributeType = $attributeType;
	}

	/**
	 * Set the attribute type name.
	 *
	 * @param string $attributeType The attribute type name.
	 *
	 * @return InputScreenAttributeIs
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
		if (!isset(self::$attributeTypes[$value]))
		{
			self::$attributeTypes[$value] = \Database::getInstance()
				->prepare('SELECT type FROM tl_metamodel_attribute WHERE id=?')
				->limit(1)
				->executeUncached($value)
				->type;
		}
		return self::$attributeTypes[$value];
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(
		ModelInterface $model = null,
		PropertyValueBag $input = null,
		PropertyInterface $property = null,
		LegendInterface $legend = null
	)
	{
		if ($input && $input->hasPropertyValue('attr_id'))
		{
			$value = $input->getPropertyValue('attr_id');
		}
		elseif ($model)
		{
			$value = $model->getProperty('attr_id');
		}
		else
		{
			return false;
		}

		return $this->getTypeOfAttribute($value) == $this->getAttributeType();
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
	}
}
