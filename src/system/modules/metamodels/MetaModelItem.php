<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Interface for a MetaModel item.
 * 
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelItem implements IMetaModelItem
{
	protected $strModelName = NULL;

	// TODO: switch to stdClass here?
	protected $arrData = array();

	/**
	 * Create a new instance.
	 * 
	 * @param IMetaModel $objMetaModel the model this item is represented by.
	 * 
	 * @param array      $arrData      the initial data that shall be injected into the new instance.
	 * 
	 * @return IMetaModelItem the instance
	 */
	public function __construct(IMetaModel $objMetaModel, $arrData)
	{
		$this->arrData = $arrData;
		$this->strModelName = $objMetaModel->getTableName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($strAttributeName)
	{
		return $this->arrData[$strAttributeName];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($strAttributeName, $varValue)
	{
		$this->arrData[$strAttributeName] = $varValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetaModel()
	{
		return MetaModelFactory::byTableName($this->strModelName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttribute($strAttributeName)
	{
		return $this->getMetaModel()->getAttribute($strAttributeName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVariant()
	{
		return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '0');
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVariantBase()
	{
		return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '1');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVariants($objFilter)
	{
		if($this->isVariantBase())
		{
			return $this->getMetaModel()->findVariants(array($this->get('id')), $objFilter);
		} else {
			return null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function save()
	{
		$objMetaModel = $this->getMetaModel();
		$objMetaModel->saveItem($this->arrData);

		// override all inherited values for all descendant items.
		if($this->isVariantBase())
		{
			$objVariants = $this->getVariants(array());
			$arrInvariantAttributes = $objMetaModel->getInVariantAttributes();
			while ($objVariants->next())
			{
				$objItem = $objVariants->getItem();
				foreach ($arrInvariantAttributes as $strAttributeId => $objAttribute)
				{
					$objItem->set($strAttributeId, $this->get($strAttributeId));
				}
				$objItem->save();
			}
		}
	}

	public function parseValue($strOutputFormat = 'html')
	{
		$arrResult = array
		(
			'raw' => $this->arrData,
			$strOutputFormat => array()
		);
		foreach($this->getMetaModel()->getAttributes() as $objAttribute)
		{
			foreach($objAttribute->parseValue($this->arrData, $strOutputFormat) as $strKey => $varValue)
			{
				$arrResult[$strKey][$objAttribute->getColName()] = $varValue;
			}
			// TODO: parseValue HOOK?
		}
		return $arrResult;
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy()
	{
		// fetch data, clean undesired fields and return the new item.
		$arrNewData = $this->arrData;
		unset($arrNewData['id']);
		unset($arrNewData['tstamp']);
		return new MetaModelItem($arrNewData);
	}

	/**
	 * {@inheritdoc}
	 */
	public function varCopy()
	{
		$objNewItem = $this->copy();
		// if this item is a variant base, we need to clean the varbase and set
		// ourselves as the base
		if ($this->isVariantBase())
		{
			$objNewItem->set('vargroup', $this->get('id'));
			$objNewItem->set('varbase', 0);
		}
	}
}

?>