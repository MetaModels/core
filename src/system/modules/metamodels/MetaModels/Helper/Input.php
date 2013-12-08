<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Backend
 * @author     Malte Gerth <mail@malte-gerth.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Helper;

/**
 * Input handling helper class
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class Input
{

    /**
     * Map of encoding input values
     * 
     * @var array
     */
    protected static $encodingMap = array(
        '/' => '-slash-',
        '\'' => '-apos-',
    );

    /**
     * Decode the given value received from the GET parameter
     * 
     * @param string  $strValue        Value to encode
     * @param boolean $blnUseUrlDecode Use urldecode to decode $strValue
     * 
     * @return string
     */
    public static function decode($strValue, $blnUseUrlDecode = true)
    {
        if ($blnUseUrlDecode)
        {
            $strValue = rawurldecode($strValue);
        }

        // Preserve the "+" as it will be used for whitespaces in
        // the Contao Input handling
        $strValue = str_replace(
            array_values(static::$encodingMap),
            array_keys(static::$encodingMap),
            $strValue
        );

        return $strValue;
    }

    /**
     * Encode the given value to be used as a GET parameter
     * 
     * @param string  $strValue        Value to encode
     * @param boolean $blnUseUrlEncode Use urlencode to encode $strValue
     * 
     * @return string
     */
    public static function encode($strValue, $blnUseUrlEncode = true)
    {
        // Preserve the "+" as it will be used for whitespaces in
        // the Contao Input handling
        $strValue = str_replace(
            array_keys(static::$encodingMap),
            array_values(static::$encodingMap),
            $strValue
        );

        if ($blnUseUrlEncode)
        {
            $strValue = rawurlencode($strValue);
        }

        return $strValue;
    }

    /**
	 * generate an url determined by the given params and configured jumpTo page.
	 *
	 * @param array $arrParams the URL parameters to use.
	 *
	 * @return string the generated URL.
	 *
	 */
	public static function getJumpToUrl($arrParams)
	{
		$strFilterAction = '';
		foreach ($arrParams as $strName => $varParam)
		{
			// skip the magic "language" parameter.
			if (($strName == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl'])
			{
				continue;
			}

			$strValue = $varParam;

			if (is_array($varParam))
			{
				$strValue = implode(',', array_filter($varParam));
			}

			// Encode the input value
            $strValue = \MetaModels\Helper\Input::encode($strValue, true);

			if (strlen($strValue))
			{
				// Shift auto_item to the front.
				if ($strName == 'auto_item')
				{
					$strFilterAction = '/' . $strValue . $strFilterAction;
					continue;
				}

				$strFilterAction .= sprintf(($GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;%s=%s' : '/%s/%s'), $strName, $strValue);
			}
		}

		return $strFilterAction;
	}
}
