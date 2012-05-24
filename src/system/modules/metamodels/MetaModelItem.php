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
	public function getVariants($arrFilter)
	{
		if($this->isVariantBase())
		{
			return $this->getMetaModel()->findVariants(array('id' => $this->get('id')), $arrFilter);
		} else {
			return null;
		}
	}
}

?>