<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use DcGeneral\DC_General;
use DcGeneral\Clipboard\ClipboardInterface;
use MetaModels\Helper\ContaoController;
use MetaModels\IMetaModel;
use MetaModels\Factory as ModelFactory;

/**
 * This class is used from DCA tl_metamodel_filtersetting for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class Filter extends Helper
{
	/**
	 * @var Filter
	 */
	protected static $objInstance = null;

	/**
	 * The MetaModel instance relevant to the current item in view.
	 *
	 * @var IMetaModel
	 */
	protected $objMetaModel = null;

	protected $strSettingType = null;

	protected $objFilter = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return Filter
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new Filter();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();

		// toggling of a filter setting?
		if(\Input::getInstance()->get('tid') && (\Input::getInstance()->get('table') == 'tl_metamodel_filtersetting'))
		{
			// Update database
			\Database::getInstance()->prepare('
				UPDATE tl_metamodel_filtersetting
				SET enabled=?
				WHERE id=?'
			)->execute(
					(\Input::getInstance()->get('state')=='1'?'1':''),
					\Input::getInstance()->get('tid')
				);
			exit;
		}
	}

	public function createDataContainer($strTableName)
	{
		$this->objectsFromUrl($strTableName);
	}

	protected function objectsFromUrl($objDC)
	{
		if (((\Input::getInstance()->get('do') != 'metamodels')
			|| ((is_object($objDC) && $objDC->table != 'tl_metamodel_filtersetting') || (!is_object($objDC) && $objDC != 'tl_metamodel_filtersetting'))))
		{
			return;
		}

		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if ($this->objMetaModel)
		{
			return;
		}

		if(is_object($objDC) && $objDC->getCurrentModel())
		{
			$this->strSettingType = $objDC->getCurrentModel()->getProperty('type');

			$this->objFilter = \Database::getInstance()
				->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
				->execute($objDC->getCurrentModel()->getProperty('fid'));

			$this->objMetaModel = ModelFactory::byId($this->objFilter->pid);
		}

		if (\Input::getInstance()->get('act'))
		{
			// act present, but we have an id
			switch (\Input::getInstance()->get('act'))
			{
				case 'edit':
					if (\Input::getInstance()->get('id'))
					{
						$this->objFilter = \Database::getInstance()->prepare('
							SELECT tl_metamodel_filter.*,
								tl_metamodel_filtersetting.type AS tl_metamodel_filtersetting_type,
								tl_metamodel_filtersetting.id AS tl_metamodel_filtersetting_id
							FROM tl_metamodel_filtersetting
							LEFT JOIN tl_metamodel_filter
							ON (tl_metamodel_filtersetting.fid = tl_metamodel_filter.id)
							WHERE (tl_metamodel_filtersetting.id=?)')
							->execute(\Input::getInstance()->get('id'));
						$this->strSettingType = $this->objFilter->tl_metamodel_filtersetting_type;
						$this->objMetaModel = ModelFactory::byId($this->objFilter->pid);
					}
					break;
				case 'paste':
					if (\Input::getInstance()->get('id'))
					{
						switch (\Input::getInstance()->get('mode'))
						{
							case 'create':
								$this->objFilter = \Database::getInstance()
									->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
									->execute(\Input::getInstance()->get('id'));

								$this->objMetaModel = ModelFactory::byId($this->objFilter->pid);
								break;
							case 'cut':
								$this->objFilter = \Database::getInstance()->prepare('
									SELECT tl_metamodel_filter.*,
										tl_metamodel_filtersetting.type AS tl_metamodel_filtersetting_type,
										tl_metamodel_filtersetting.id AS tl_metamodel_filtersetting_id
									FROM tl_metamodel_filtersetting
									LEFT JOIN tl_metamodel_filter
									ON (tl_metamodel_filtersetting.fid = tl_metamodel_filter.id)
									WHERE (tl_metamodel_filtersetting.id=?)')
									->execute(\Input::getInstance()->get('source'));
								$this->strSettingType = $this->objFilter->tl_metamodel_filtersetting_type;
								$this->objMetaModel = ModelFactory::byId($this->objFilter->pid);
								break;
						}
					}
					break;
				case 'select':
				case 'create':
					$this->objFilter = \Database::getInstance()
						->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
						->execute(\Input::getInstance()->get('id'));

					$this->objMetaModel = ModelFactory::byId($this->objFilter->pid);
					break;

				default:;
			}
		} else {
			// no act but we have an id, should be list mode then, no type name available.
			if (\Input::getInstance()->get('id'))
			{
				$this->objFilter = \Database::getInstance()->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')->execute(\Input::getInstance()->get('id'));
				$this->objMetaModel = ModelFactory::byId($this->objFilter->pid);
			}
		}

		// select all root entries for the current filter.
		$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['list']['sorting']['root'] =
			\Database::getInstance()->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE fid=? AND pid=0')
				->execute($this->objFilter->id)
				->fetchEach('id');
		$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['list']['sorting']['rootPaste'] = true;

		if ($this->objMetaModel)
		{
			$GLOBALS['TL_LANG']['MSC']['editRecord'] = sprintf(
				$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord'],
				$this->objFilter->name,
				$this->objMetaModel->getName()
			);

			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['config']['label'] = sprintf(
				$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label'],
				$this->objFilter->name,
				$this->objMetaModel->getName()
			);
		}
	}

	/**
	 * Retrieve the MetaModel instance relevant for the current filter setting in view.
	 *
	 * @param \DcGeneral\DC_General $objDC The data container.
	 *
	 * @return IMetaModel
	 */
	public function getMetaModel($objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel;
	}

	/**
	 * Translates an attribute id to the human readable name defined.
	 *
	 * @param $strValue
	 *
	 * @param $objDC
	 *
	 * @return string the human readable name.
	 */
	public function attrIdToHumanName($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel->getAttributeById($strValue)->getName();
	}

	/**
	 * Prepares the sub palettes for simple look up filter setting types.
	 *
	 * @return void
	 */
	public function preparePalettes()
	{
		$this->objectsFromUrl(null);
		if (!($this->objMetaModel && $this->objFilter))
		{
			return;
		}
		$objMetaModel = $this->objMetaModel;

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$strSelectVal = $objMetaModel->getTableName() .'_' . $objAttribute->getColName();
			if ($GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$this->strSettingType . '_palettes'][$strTypeName])
			{
				$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metasubselectpalettes']['attr_id'][$strSelectVal] = $GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$this->strSettingType . '_palettes'][$strTypeName];
			}
		}
	}

	/**
	 * get frontend templates for filter elements
	 *
	 * @param \DcGeneral\DC_General
	 *
	 * @return array
	 */
	public function getSubTemplates(DC_General $dc)
	{
		return $this->getTemplatesForBase('mm_filteritem_');
	}

	/**
	 * Set the parent condition for the current fid.
	 *
	 * @param string     $strTable The tablename - must be tl_metamodel_filtersetting.
	 *
	 * @return string The value "tl_metamodel_filtersetting".
	 */
	public function loadTableCallback($strTable)
	{
		if ($strTable == 'tl_metamodel_filtersetting')
		{
			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['dca_config']['childCondition'][0]['filter'][] = array
			(
				'local'        => 'fid',
				'remote_value' => \Input::getInstance()->get('id'),
				'operation'    => '=',
			);

			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['dca_config']['rootEntries']['tl_metamodel_filtersetting']['setOn'][] = array
			(
				'property'    => 'fid',
				'value'       => \Input::getInstance()->get('id'),
			);

			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['dca_config']['rootEntries']['tl_metamodel_filtersetting']['filter'][] = array
			(
				'property'    => 'fid',
				'operation'   => '=',
				'value'       => \Input::getInstance()->get('id'),
			);
		}
		return $strTable;
	}

	/**
	 * when creating a new item, we need to populate the fid column.
	 */
	public function create_callback($strTable, $insertID, $arrRow, $objDC)
	{
		// If we come from overview use pid
		if(\Input::getInstance()->get('id') != "")
		{
			$intFid = \Input::getInstance()->get('id');
		}
		// If we use the "save and new" btt use the pid instead
		elseif(\Input::getInstance()->get('pid') != "")
		{
			// Get fid from pid
			$arrFid = \Database::getInstance()
				->prepare('SELECT fid FROM tl_metamodel_filtersetting WHERE id=?')
				->execute(\Input::getInstance()->get('pid'))
				->fetchEach('fid');

			// Check if we have a pid
			if(count($arrFid) == 0)
			{
				throw new \Exception("Could not find FID. Please create a new entry from main overview.");
			}

			// Set fid by pid`s fid
			$intFid = $arrFid[0];
		}

		\Database::getInstance()->prepare('UPDATE tl_metamodel_filtersetting %s WHERE id=?')
			->set(array('fid' => $intFid))
			->execute($insertID);
	}

	protected $objCallback = null;

	public function drawSetting($arrRow, $strLabel, DC_General $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		$strImage = $GLOBALS['METAMODELS']['filters'][$arrRow['type']]['image'];

		if (!$strImage || !file_exists(TL_ROOT . '/' . $strImage))
		{
			$strImage = 'system/modules/metamodels/html/filter_default.png';
		}

		if (!$arrRow['enabled'])
		{
			$intPos=strrpos($strImage, '.');
			if ($intPos !== false)
			{
				$strImage = substr_replace($strImage, '_1', $intPos, 0);
			}
		}

		// Return the image only
		if ($blnReturnImage)
		{
			return ContaoController::getInstance()->generateImage($strImage, '', $imageAttribute);
		}

		$strImage = ContaoController::getInstance()->generateImage($strImage, '', $imageAttribute);
		$strLabel = $GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames'][$arrRow['type']];

		// if a setting wants to render itself, let it do so.
		if ($GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'])
		{

			$strClass    = $GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'][0];
			$objCallback = (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();

			$strReturn = $objCallback->{$GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'][1]}(
				$arrRow,
				$strLabel,
				$objDC,
				$imageAttribute,
				$strImage
			);

		} else {
			if(!empty($arrRow['comment']))
			{
				$arrRow['comment'] = sprintf($GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_'], specialchars($arrRow['comment']));
			}

			$strReturn = sprintf(
				$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_default_'],
				'<a href="' . $this->addToUrl('act=edit&amp;id='.$arrRow['id']). '">' . $strImage . '</a>',
				$strLabel ? $strLabel : $arrRow['type'],
				$arrRow['comment'],
				$arrRow['type']
			);
		}
		return $strReturn;
	}

	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['enabled'] ? '0' : '1');
		if (!$row['enabled'])
		{
			$icon = 'invisible.gif';
		}
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.ContaoController::getInstance()->generateImage($icon, $label).'</a> ';
	}
}
