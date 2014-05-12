<?php

namespace MetaModels\BackendIntegration\InputScreen;

use MetaModels\IMetaModel;

/**
 * Implementation of IInputScreen.
 *
 * @package MetaModels\BackendIntegration\InputScreen
 */
class InputScreen implements IInputScreen
{
	/**
	 * The data for the input screen.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The legends contained within the input screen.
	 *
	 * @var array
	 */
	protected $legends = array();

	/**
	 * The properties contained within the input screen.
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Create a new instance.
	 *
	 * @param array $data         The information about the input screen.
	 *
	 * @param array $propertyRows The information about all contained properties.
	 */
	public function __construct($data, $propertyRows)
	{
		$this->data = $data;

		$this->translateRows($propertyRows);
	}

	/**
	 * Transform a legend information into the property legends.
	 *
	 * @param array      $legend    The legend to transform.
	 *
	 * @param IMetaModel $metaModel The metamodel the legend belongs to.
	 *
	 * @return string
	 */
	public function translateLegend($legend, $metaModel)
	{
		$arrLegend = deserialize($legend['legendtitle']);
		if (is_array($arrLegend))
		{
			// Try to use the language string from the array.
			$strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];
			if (!$strLegend)
			{
				// Use the fallback.
				$strLegend = $arrLegend[$metaModel->getFallbackLanguage()];
				if (!$strLegend)
				{
					// Last resort, simply "legend".
					$strLegend = 'legend';
				}
			}
		}
		else
		{
			$strLegend = $legend['legendtitle'] ? $legend['legendtitle'] : 'legend';
		}

		$legendName = standardize($strLegend);

		$this->legends[$legendName] = array
		(
			'name'       => $strLegend,
			'visible'    => (bool)$legend['legendhide'],
			'properties' => array()
		);

		return $legendName;
	}

	/**
	 * Translate a property.
	 *
	 * @param array      $property    The property information to transform.
	 *
	 * @param IMetaModel $metaModel   The MetaModel the property belongs to.
	 *
	 * @param string     $legend      The legend the property belongs to.
	 *
	 * @param array      $columnNames The column name information.
	 *
	 * @return void
	 */
	public function translateProperty($property, $metaModel, $legend, $columnNames)
	{
		$attribute = $metaModel->getAttributeById($property['attr_id']);

		// Dead meat.
		if (!$attribute)
		{
			return;
		}

		$propName = $attribute->getColName();

		if ($property['subpalette'])
		{
			// This should never ever be true. If so, we have dead entries in the database.
			if (!isset($columnNames[$property['subpalette']]))
			{
				return;
			}

			$parentColumn = $columnNames[$property['subpalette']];

			$this->properties[$parentColumn]['subpalette'][] = $propName;
		}
		else
		{
			$this->legends[$legend]['properties'][] = $propName;
		}

		$this->properties[$propName] = array
		(
			'info'       => $attribute->getFieldDefinition($property),
			'subpalette' => array()
		);
	}

	/**
	 * Translate database rows into legend and property information.
	 *
	 * @param array $rows The database rows.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException When an unknown palette rendering mode is encountered (neither 'legend' nor 'attribute').
	 */
	public function translateRows($rows)
	{
		$metaModel    = $this->getMetaModel();
		$activeLegend = null;

		// First pass, fetch all attribute names.
		$columnNames = array();
		foreach ($rows as $row)
		{
			if ($row['dcatype'] != 'attribute')
			{
				continue;
			}

			$attribute = $metaModel->getAttributeById($row['attr_id']);
			if ($attribute)
			{
				$columnNames[$row['id']] = $attribute->getColName();
			}
		}

		// Second pass, translate all information into local properties.
		foreach ($rows as $row)
		{
			switch ($row['dcatype'])
			{
				case 'legend':
					$activeLegend = $this->translateLegend($row, $metaModel);
					break;
				case 'attribute':
					$this->translateProperty($row, $metaModel, $activeLegend, $columnNames);
					break;
				default:
					throw new \RuntimeException('Unknown palette rendering mode ' . $row['dcatype']);
			}
		}

		// Third pass, set submitOnChange for all sub palette fields.
		foreach ((array)$this->properties as $propName => $propInfo)
		{
			if (!empty($propInfo['subpalette']))
			{
				$this->properties[$propName]['info']['submitOnChange'] = true;
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId()
	{
		return $this->data['id'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLegends()
	{
		return $this->legends;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLegendNames()
	{
		return array_keys($this->legends);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLegend($name)
	{
		return $this->legends[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperty($name)
	{
		return $this->properties[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		$result = array();
		foreach ($this->getLegends() as $legend)
		{
			$result = array_merge($result, $legend['properties']);
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMetaModel()
	{
		return \MetaModels\Factory::byId($this->data['pid']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIcon()
	{
		// Determine image to use.
		if ($this->data['backendicon'])
		{
			return $this->data['backendicon'];
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBackendSection()
	{
		return trim($this->data['backendsection']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBackendCaption()
	{
		return deserialize($this->data['backendcaption'], true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParentTable()
	{
		return $this->data['ptable'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function isStandalone()
	{
		return $this->data['rendertype'] == 'standalone';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMode()
	{
		// If we have variant overwrite all modes and set mode 5 - treemode.
		$objMetaModels = $this->getMetaModel();
		if ($objMetaModels->hasVariants())
		{
			return 5;
		}
		else
		{
			return $this->data['mode'];
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function isClosed()
	{
		return $this->data['closed'];
	}
}
