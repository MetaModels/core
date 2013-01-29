<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Form field "tags", based on form field by Leo Feyer
 *
 * @package	   MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class WidgetTags extends Widget
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
	protected $strTemplate = 'form_widget';


	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'maxlength':
				if ($varValue > 0)
				{
					$this->arrAttributes['maxlength'] =  $varValue;
				}
				break;

			case 'mandatory':
				if ($varValue)
				{
					$this->arrAttributes['required'] = 'required';
				}
				else
				{
					unset($this->arrAttributes['required']);
				}
				parent::__set($strKey, $varValue);
				break;

			case 'placeholder':
				$this->arrAttributes['placeholder'] = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Trim values
	 * @param mixed
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		if (is_array($varInput))
		{
			return parent::validator($varInput);
		}

		return parent::validator(trim($varInput));
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$return = sprintf('<fieldset id="ctrl_%s" class="checkbox_container">
		',
		$this->strName
		);

		if($this->options && is_array($this->options))
		{
			// do not filter
			$return .= sprintf('<span class="%s none"><input type="checkbox" name="%s[]" id="opt_%s" class="checkbox" value="%s"%s%s <label id="lbl_%s" for="opt_%s">%s</label></span>',
				$this->strName,
				$this->strName,
				$this->strName.'_0',
				'--none--',
				'',
				$this->strTagEnding,
				$this->strName.'_0',
				$this->strName.'_0',
				$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter']
				);

			// select all tags
			$return .= sprintf('<span class="%s all"><input type="checkbox" name="%s[]" id="opt_%s" class="checkbox" value="%s"%s%s <label id="lbl_%s" for="opt_%s">%s</label></span>',
				$this->strName,
				$this->strName,
				$this->strName.'_1',
				'--all--',
				'',
				$this->strTagEnding,
				$this->strName.'_1',
				$this->strName.'_1',
				$GLOBALS['TL_LANG']['metamodels_frontendfilter']['select_all']
				);

			$count = 2;
			foreach($this->options as $key=>$val)
			{
				$return .= sprintf('<span class="%s opt_%s"><input type="checkbox" name="%s[]" id="opt_%s" class="checkbox" value="%s"%s%s <label id="lbl_%s" for="opt_%s">%s</label></span>',
					$this->strName,
					$count,
					$this->strName,
					$this->strName.'_'.$count,
					$val['value'],
					(is_array($this->varValue) ? (in_array($val['value'],$this->varValue) ? ' checked="checked"' : ''):''),
					$this->strTagEnding,
					$this->strName.'_'.$count,
					$this->strName.'_'.$count,
					$val['label']
					);
			}
		}
		else
		{
			// do not filter
			$return .= sprintf('<span class="%s none"><input type="checkbox" name="%s[]" id="opt_%s" class="checkbox" value="%s"%s%s <label id="lbl_%s" for="opt_%s">%s</label></span>',
				$this->strName,
				$this->strName,
				$this->strName.'_0',
				'--none--',
				'',
				$this->strTagEnding,
				$this->strName.'_0',
				$this->strName.'_0',
				$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'].'<span>'.$GLOBALS['TL_LANG']['metamodels_frontendfilter']['no_combinations'].'</span>'
				);
		}

		$return .='</fieldset>';

		return $return;
	}
}

