<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Widgets;

/**
 * Form field with more than 1 input, based on form field by Leo Feyer
 *
 * @package	   MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class MultiTextWidget extends \Widget
{
	/**
	 * Submit user input.
	 *
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * The template to use.
	 *
	 * @var string
	 */
	protected $strTemplate = 'form_widget';

	/**
	 * Add specific attributes.
	 *
	 * @param string $strKey   Name of the key to set.
	 *
	 * @param mixed  $varValue The value to use.
	 *
	 * @return void
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
	 * Trim the values and validate them.
	 *
	 * @param mixed $varInput The value to process.
	 *
	 * @return mixed The processed value
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
	 * Generate the widget and return it as string.
	 *
	 * @return string
	 */
	public function generate()
	{
		$return = '';
		for ($i = 0; $i < $this->size; $i++)
		{
			$return .= sprintf('<input type="%s" name="%s[]" id="ctrl_%s_%s" class="text%s%s" value="%s"%s%s',
				'text',
				$this->strName,
				$this->strId,
				$i,
				'',
				(strlen($this->strClass) ? ' ' . $this->strClass : ''),
				specialchars($this->varValue[$i]),
				$this->getAttributes(),
				$this->strTagEnding);
		}

		return $return;
	}
}
