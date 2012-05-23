<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
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
 * This is the main MetaModels-attribute base class.
 * To create a MetaModelAttribute instance, use the {@link MetaModelAttributeFactory}
 * This class is the reference implementation for {@link IMetaModelAttribute}.
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttribute implements IMetaModelAttribute
{

	/**
	 * the MetaModel instance this object belongs to.
	 * 
	 * @var IMetaModel
	 */
	protected $objMetaModel = null;

	/**
	 * The meta information of this attribute.
	 * 
	 * @var array
	 */
	protected $arrData=array();

	/**
	 * instantiate an metamodel attribute.
	 * Note that you should not use this directly but use the factory classes to instantiate attributes.
	 * 
	 * @param IMetaModel $objMetaModel the IMetaModel instance this attribute belongs to.
	 * 
	 * @param array $arrData the information array, for attribute information, refer to documentation of table tl_metamodel_attribute
	 *                       and documentation of the certain attribute classes for information what values are understood.
	 */
	public function __construct(IMetaModel $objMetaModel, $arrData = array())
	{
		// meta information
		foreach(self::getAttributeSettingNames() as $strSettingName)
		{
			if(isset($arrData[$strSettingName]))
			{
				$this->set($strSettingName, $arrData[$strSettingName]);
			}
		}
		$this->objMetaModel = $objMetaModel;
	}

	/**
	 * Retrieve the human readable name (or title) from the attribute.
	 * 
	 * If the MetaModel is translated, the currently active language is used,
	 * with properly falling back to the defined fallback language.
	 * 
	 * @return string the human readable name
	 */
	public function getName()
	{
		if (is_array($this->arrData['name']))
		{
			return $this->getLangValue($this->get('name'), $strLang);
		}
		return $this->arrData['name'];
	}

	/**
	 * This extracts the value for the given language from the given language array.
	 * 
	 * If the language is not contained within the value array, the fallback language from the parenting {@link IMetaModel}
	 * instance is tried as well.
	 * 
	 * @param array  $arrValues the array holding all language values in the form array('langcode' => $varValue)
	 * 
	 * @param string $strLangCode The language code of the language to fetch. Optional, if not given, $GLOBALS['TL_LANGUAGE'] is used.
	 * 
	 * @return mixed|null the value for the given language or the fallback language, NULL if neither is present.
	 */
	protected function getLangValue($arrValues, $strLangCode = NULL)
	{
		if ($strLangCode === NULL)
		{
			return $this->getLangValue($arrValues, $GLOBALS['TL_LANGUAGE']);
		}

		if (array_key_exists($strLangCode, $arrValues))
		{
			return $arrValues[$strLangCode];
		} else {
			$arrKeys = array_keys($arrValues);
			// lang code not set, use fallback.
			return $arrValues[$this->getMetaModel()->getFallbackLanguage()];
		}
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttribute
	/////////////////////////////////////////////////////////////////

	/**
	 * {@inheritdoc}
	 */
	public function getColName()
	{
		return $this->arrData['colName'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetaModel()
	{
		return $this->objMetaModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($strKey)
	{
		return $this->arrData[$strKey];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($strKey, $varValue)
	{
		if (in_array($strKey, self::getAttributeSettingNames()))
			$this->arrData[$strKey] = deserialize($varValue);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleMetaChange($strMetaName, $varNewValue)
	{
		// by default we accept any change of meta information.
		$this->set($strMetaName, $varNewValue);
		
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroyAUX()
	{
		// no-op
	}

	/**
	 * {@inheritdoc}
	 */
	public function initializeAUX()
	{
		// no-op
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getAttributeSettingNames()
	{
		return array('id', 'pid', 'sorting', 'tstamp', 'name', 'description', 'type', 'colName');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldDefinition()
	{
		// only overwrite the language if not already set.
		if(!$GLOBALS['TL_LANG'][$this->getMetaModel()->getTableName()][$this->getColName()])
		{
			$GLOBALS['TL_LANG'][$this->getMetaModel()->getTableName()][$this->getColName()] = array
			(
				$this->getLangValue($this->get('name'), $strLang), 
				$this->getLangValue($this->get('description'), $strLang),
			);
		}
		$arrFieldDef = array(
			'label' => &$GLOBALS['TL_LANG'][$this->getMetaModel()->getTableName()][$this->getColName()],
			'flag' => '1',
			'eval'  => array()
		);

		// TODO: this is not used currently.
		$arrFieldDef['eval']['mandatory'] = $arrFieldDef['eval']['mandatory'] || ($this->mandatory && in_array('mandatory', $visibleOptions) ? true : false);
		return $arrFieldDef;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItemDCA()
	{
		return array('fields' => array_merge(
			array(
				$this->getColName() => $this->getFieldDefinition())
			),
			(array)$GLOBALS['TL_DCA'][$this->getMetaModel()->getTableName()]['fields'][$this->getColName()]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseValue($arrRowData)
	{
		return array('html' => $arrRowData[$this->getColName()]);
	}
}

?>