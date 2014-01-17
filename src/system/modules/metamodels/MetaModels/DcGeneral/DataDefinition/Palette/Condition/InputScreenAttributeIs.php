<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

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

	function __construct($attributeType)
	{
		$this->attributeType = $attributeType;
	}

	/**
	 * @param mixed $attributeType
	 *
	 * @return InputScreenAttributeIs
	 */
	public function setAttributeType($attributeType)
	{
		$this->attributeType = $attributeType;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAttributeType()
	{
		return $this->attributeType;
	}

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
	public function match(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($input && $input->hasPropertyValue('attr_id')) {
			$value = $input->getPropertyValue('attr_id');
		}
		else if ($model) {
			$value = $model->getProperty('attr_id');
		}
		else {
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
