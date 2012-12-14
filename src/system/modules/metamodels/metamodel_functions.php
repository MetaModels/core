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

// as of PHP 5.3.0 array_replace_recursive() does the work for us
if (!function_exists('array_replace_recursive'))
{

	/**
	 * Recursive helper function for @see{array_replace_recursive()}
	 *
	 * @param array $array  the array that shall be overwritten
	 * @param array $array1 the array that holds the values that shall overwrite the values in $array
	 *
	 * @return array the resulting array.
	 */
	function array_replace_recursive_recurse($array, $array1)
	{
		foreach ($array1 as $key => $value)
		{
			// create new key in $array, if it is empty or not an array
			if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
			{
				$array[$key] = array();
			}
			// overwrite the value in the base array
			if (is_array($value))
			{
				$value = array_replace_recursive_recurse($array[$key], $value);
			}
			$array[$key] = $value;
		}
		return $array;
	}

	/**
	 * array_replace_recursive — Replaces elements from passed arrays into the first array recursively.
	 * This is a work around implementation for PHP <5.3
	 *
	 * @author Gregor Meyer <gregor@der-meyer.de> (found on php.net)
	 *
	 * @link http://php.net/manual/de/function.array-replace-recursive.php
	 *
	 */
	function array_replace_recursive(/* $array, ... */)
	{
		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if (!is_array($array))
		{
			return $array;
		}
		for ($i = 1; $i < count($args); $i++)
		{
			if (is_array($args[$i]))
			{
				$array = array_replace_recursive_recurse($array, $args[$i]);
			}
		}
		return $array;
	}
}

