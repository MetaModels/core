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
	protected $strTemplate = 'be_widget';

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
	 * Initialize the object
	 * @param array
	 */
	public function __construct($arrAttributes=false)
	{
		parent::__construct();
		// Input field callback
		if (is_array($arrAttributes['getsubfields_callback']))
		{
			if (!is_object($this->$arrAttributes['getsubfields_callback'][0]))
			{
				$this->import($arrAttributes['getsubfields_callback'][0]);
			}
			$this->arrSubFields=$this->$arrAttributes['getsubfields_callback'][0]->$arrAttributes['getsubfields_callback'][1]($this, $arrAttributes);
		}
		$this->addAttributes($arrAttributes);
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

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$arrOptions = array();
		if(is_array($this->arrSubFields))
		{
			foreach ($this->arrSubFields as $i=>$arrSubField)
			{
				// generate widget.
				if(isset($arrSubField['inputType']) && isset($GLOBALS['BE_FFL'][$arrSubField['inputType']]))
				{
					$widgettype=$arrSubField['inputType'];
					// force checkbox to select with yes/no
					if($widgettype=='checkbox')
					{
						$widgettype='select';
						$arrSubField['inputType']='select';
						$arrSubField['eval']['options']=array(
																array('value' => 1, 'label' => $GLOBALS['TL_LANG']['MSC']['yes']),
																array('value' => '', 'label' => $GLOBALS['TL_LANG']['MSC']['no'])
															);
					}
					$parsedSubField=$this->prepareForWidget($arrSubField, $this->strName . '_'.$i, $this->value[$i], $this->strField . '['.$i.']', $this->strTable);
					$tmp=new $GLOBALS['BE_FFL'][$widgettype]($parsedSubField);
					$parsedSubField['name']=$this->strName . '['.$i.']';
					$widget =$tmp->parse($parsedSubField);
				} else {
					$widget = '<input type="hidden" name="'. $this->strName . '['.$i.']" class="tl_multitext" value="'.specialchars($this->varValue[$i]).'"' . ' />';
				}

				$arrOptions[]='<div class="'.$arrSubField['eval']['tl_class'].'">'.$widget.'</div>';
			}
		}

		// Add a "no entries found" message if there are no sub widgets
		if (!count($arrOptions))
		{
			$arrOptions[]= '<p class="tl_noopt">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

        return sprintf('<div id="ctrl_%s" class="tl_multiwidget_container%s clr">%s</div>',
						$this->strName,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						implode("\n", $arrOptions));
	}
}
?>