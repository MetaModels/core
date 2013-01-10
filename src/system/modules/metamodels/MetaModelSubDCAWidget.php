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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Class multiWidget
 *
 * Provide methods to handle multiple widgets in one.
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelSubDCAWidget extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_subdca';

	/**
	 * Options
	 * @var array
	 */
	protected $arrOptions = array();

	/**
	 * SubFields
	 * @var array
	 */
	protected $arrSubFields = array();

	/**
	 * Flag fields to be applied to each subfield.
	 * @var array
	 */
	protected $arrFlagFields = array();

	/**
	 * The prepared widgets
	 *
	 * @var array
	 */
	protected $arrWidgets = array();

	/**
	 * Initialize the object
	 * @param array
	 */
	public function __construct($arrAttributes=false)
	{
		parent::__construct();
		$this->addAttributes($arrAttributes);
		// Input field callback
		if (is_array($arrAttributes['getsubfields_callback']))
		{
			if (!is_object($this->$arrAttributes['getsubfields_callback'][0]))
			{
				$this->import($arrAttributes['getsubfields_callback'][0]);
			}
			$this->arrSubFields=$this->$arrAttributes['getsubfields_callback'][0]->$arrAttributes['getsubfields_callback'][1]($this, $arrAttributes);
		}
	}

	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'options':
				$this->arrOptions = deserialize($varValue);

				foreach ($this->arrOptions as $arrOptions)
				{
					if ($arrOptions['default'])
					{
						$this->varValue = $arrOptions['value'];
					}
				}
				break;
			case 'subfields':
				$this->arrSubFields = deserialize($varValue);
				break;

			case 'flagfields':
				$this->arrFlagFields = deserialize($varValue);
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}

	/**
	 * Initialize widget
	 *
	 * Based on DataContainer::row() from Contao 2.10.1
	 *
	 * @param	array        $arrField the field DCA
	 * @param	string       $strRow   the setting name
	 * @param	string       $strKey   the widget name
	 * @param	mixed        $varValue the widget value
	 * @return	Widget|null
	 */
	protected function initializeWidget(&$arrField, $strRow, $strKey, $varValue)
	{
		$xlabel = '';
		$strContaoPrefix = 'contao/';

		// Toggle line wrap (textarea)
		if ($arrField['inputType'] == 'textarea' && $arrField['eval']['rte'] == '')
		{
			$xlabel .= ' ' . $this->generateImage(
				'wrap.gif',
				$GLOBALS['TL_LANG']['MSC']['wordWrap'],
				sprintf(
					'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s_%s_%s\');"',
					specialchars($GLOBALS['TL_LANG']['MSC']['wordWrap']),
					$this->strId,
					$strRow,
					$strKey
				)
			);
		}

		// Add the help wizard
		if ($arrField['eval']['helpwizard'])
		{
			$xlabel .= sprintf(
				' <a href="%shelp.php?table=%s&amp;field=%s_%s" title="%s" rel="lightbox[help 610 80%]">%s</a>',
				$strContaoPrefix,
				$this->strTable,
				$this->strName,
				$strKey,
				specialchars($GLOBALS['TL_LANG']['MSC']['helpWizard']),
				$this->generateImage('about.gif', $GLOBALS['TL_LANG']['MSC']['helpWizard'], 'style="vertical-align:text-bottom;"')
			);
		}

		// Add the popup file manager
		if ($arrField['inputType'] == 'fileTree')
		{
			$path = '';

			if (isset($arrField['eval']['path']))
			{
				$path = '?node=' . $arrField['eval']['path'];
			}

			$xlabel .= sprintf(
				' <a href="%sfiles.php%s" title="%s" rel="lightbox[files 765 80%]">%s</a>',
				$strContaoPrefix,
				$path,
				specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']),
				$this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"')
			);
		}
		// Add the table import wizard
		elseif ($arrField['inputType'] == 'tableWizard')
		{
			$xlabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
				$this->addToUrl('key=table'),
				specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][1]),
				$this->generateImage('tablewizard.gif', $GLOBALS['TL_LANG']['MSC']['tw_import'][0], 'style="vertical-align:text-bottom;"')
			);
			$xlabel .= ' ' .
				$this->generateImage(
					'demagnify.gif',
					'',
					sprintf('title="%s" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(0.9);"', specialchars($GLOBALS['TL_LANG']['MSC']['tw_shrink']))
				) .
				$this->generateImage(
					'magnify.gif',
					'',
					sprintf('title="%s" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(1.1);"', specialchars($GLOBALS['TL_LANG']['MSC']['tw_expand']))
				);
		}
		// Add the list import wizard
		elseif ($arrField['inputType'] == 'listWizard')
		{
			$xlabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
				$this->addToUrl('key=list'),
				specialchars($GLOBALS['TL_LANG']['MSC']['lw_import'][1]),
				$this->generateImage('tablewizard.gif', $GLOBALS['TL_LANG']['MSC']['tw_import'][0], 'style="vertical-align:text-bottom;"')
			);
		}

		// Input field callback
		if (is_array($arrField['input_field_callback']))
		{
			if (!is_object($this->$arrField['input_field_callback'][0]))
			{
				$this->import($arrField['input_field_callback'][0]);
			}

			return $this->$arrField['input_field_callback'][0]->$arrField['input_field_callback'][1]($this, $xlabel);
		}

		$strClass = $GLOBALS[(TL_MODE == 'BE' ? 'BE_FFL' : 'TL_FFL')][$arrField['inputType']];

		if ($strClass == '' || !$this->classFileExists($strClass))
		{
			return null;
		}

		$arrField['eval']['required'] = false;

		// Use strlen() here (see #3277)
		if ($arrField['eval']['mandatory'])
		{
			if (is_array($this->varValue[$strRow][$strKey]))
			{
				if (empty($this->varValue[$strRow][$strKey]))
				{
					$arrField['eval']['required'] = true;
				}
			}
			else
			{
				if (!strlen($this->varValue[$strRow][$strKey]))
				{
					$arrField['eval']['required'] = true;
				}
			}
		}

		// load callback
		if (is_array($arrField['load_callback']))
		{
			foreach ($arrField['load_callback'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $this);
			}
		}

		// TODO: add initialization of tinyMCE, datepicker, colorpicker etc.

		$arrField['name'] = $this->strName . '[' . $strRow . '][' . $strKey . ']';
		$arrField['id'] = $this->strId . '_' . $strRow . '_' . $strKey;
		$arrField['value'] = ($varValue !== '') ? $varValue : $arrField['default'];
		$arrField['eval']['tableless'] = true;

		$objWidget = new $strClass($this->prepareForWidget($arrField, $arrField['name'], $arrField['value'], null, $this->strTable));

		$objWidget->strId = $arrField['id'];
		$objWidget->storeValues = true;
		$objWidget->xlabel = $xlabel;

		return $objWidget;
	}

	protected function prepareWidgets()
	{
		if ($this->arrWidgets)
		{
			return;
		}

		$arrWidgets = array();
		foreach ($this->arrSubFields as $strFieldName => &$arrSubField)
		{
			$varValue = $this->value[$strFieldName];
			$arrRow = array();
			$objWidget = $this->initializeWidget($arrSubField, $strFieldName, 'value', $varValue['value']);
			if (!$objWidget)
			{
				continue;
			}
			$arrRow[] = $objWidget;
			foreach ($this->arrFlagFields as $strFlag => $arrFlagField)
			{
//				unset($arrFlagField['label']);
				$objWidget = $this->initializeWidget($arrFlagField, $strFieldName, $strFlag, $varValue[$strFlag]);

				if ($objWidget)
				{
					$arrRow[] = $objWidget;
				}
			}
			$arrWidgets[] = $arrRow;
		}
		$this->arrWidgets = $arrWidgets;
	}

	/**
	 * Initialize widget
	 *
	 * Based on DataContainer::row() from Contao 2.10.1
	 *
	 * @param	array        $arrField the field DCA
	 * @param	string       $strRow   the setting name
	 * @param	string       $strKey   the widget name
	 * @param	mixed        $varInput the overall input value
	 * @return	Widget|null
	 */
	protected function validateWidget(&$arrField, $strRow, $strKey, &$varInput)
	{
		$varValue = $varInput[$strRow][$strKey];
		$objWidget = $this->initializeWidget($arrField, $strRow, $strKey, $varValue);
		if (!is_object($objWidget))
		{
			return;
		}

		// hack for checkboxes
		if ($arrField['inputType'] == 'checkbox' && isset($varInput[$strRow][$strKey]))
		{
			$_POST[$objWidget->name] = $varValue;
		}

		$objWidget->validate();

		$varValue = $objWidget->value;

		// Convert date formats into timestamps (check the eval setting first -> #3063)
		$rgxp = $arrField['eval']['rgxp'];
		if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '')
		{
			$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$rgxp . 'Format']);
			$varValue = $objDate->tstamp;
		}

		// Save callback
		if (is_array($arrField['save_callback']))
		{
			foreach ($arrField['save_callback'] as $callback)
			{
				$this->import($callback[0]);

				try
				{
					$varValue = $this->$callback[0]->$callback[1]($varValue, $this);
				}
				catch (Exception $e)
				{
					$objWidget->class = 'error';
					$objWidget->addError($e->getMessage());
				}
			}
		}

		$varInput[$strRow][$strKey] = $varValue;

		// Do not submit if there are errors
		if ($objWidget->hasErrors())
		{
			// store the errors
			$this->arrWidgetErrors[$strKey][$strRow] = $objWidget->getErrors();

			return false;
		}
		return true;
	}

	/**
	 * Validate the widget.
	 */
	protected function validator($varInput)
	{
		$blnHasError = false;
		foreach ($this->arrSubFields as $strFieldName => &$arrSubField)
		{
			if (!$this->validateWidget($arrSubField, $strFieldName, 'value', $varInput))
			{
				$blnHasError = true;
			}

			foreach ($this->arrFlagFields as $strFlag => $arrFlagField)
			{
				if (!$this->validateWidget($arrFlagField, $strFieldName, $strFlag, $varInput))
				{
					$blnHasError = true;
				}
			}
		}

		if ($blnHasError)
		{
			$this->blnSubmitInput = false;
			$this->addError($GLOBALS['TL_LANG']['ERR']['general']);
		}
		return $varInput;
	}

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_CSS'][] = 'system/modules/metamodels/html/style.css';

		$this->prepareWidgets();

		$arrOptions = array();
		foreach ($this->arrWidgets as $arrWidgetRow)
		{
			$arrColumns = array();
			foreach ($arrWidgetRow as $objWidget)
			{
				$arrColumns[] = sprintf(
					'<td %1$s%2$s%3$s>%4$s%5$s</td>',
					($objWidget->valign != '' ? ' valign="' . $objWidget->valign . '"' : ''),
					($objWidget->tl_class != '' ? ' class="' . $objWidget->tl_class . '"' : ''),
					($objWidget->style != '' ? ' style="' . $objWidget->style . '"' : ''),
					$objWidget->parse(),
					($GLOBALS['TL_CONFIG']['showHelp'] && $objWidget->description) ? '<p class="tl_help tl_tip' . $objWidget->tl_class . '">'.$objWidget->description.'</p>' : ''
				);
			}
			$arrOptions[] = implode('', $arrColumns);
		}

		// Add a "no entries found" message if there are no sub widgets
		if (!count($arrOptions))
		{
			$arrOptions[]= '<p class="tl_noopt">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		$strHead = '';
/*
		$strHead = '<thead><tr><th>Value</th>';
		foreach ($this->arrFlagFields as $arrFlag)
		{
			$strHead .= '<th>'.$arrFlag['label'][0].'</th>';
		}
		$strHead .= '</tr></thead>';
*/
		$strBody = sprintf('<tbody><tr>%s</tr></tbody>', implode("</tr>\n<tr>", $arrOptions));

		$strOutput .= sprintf(
			'<table cellspacing="0"%s cellpadding="0" id="ctrl_%s" class="tl_modulewizard multicolumnwizard" summary="MultiColumnWizard">%s%s</table>',
			(($this->style) ? ('style="' . $this->style . '"') : ('')),
			$this->strId,
			$strHead,
			$strBody
		);

		return sprintf('<div id="ctrl_%s" class="tl_multiwidget_container%s clr">%s</div>',
						$this->strName,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						$strOutput);
	}
}

