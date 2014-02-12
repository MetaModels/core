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

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\Helper\ContaoController;

/**
 * This filter condition generates a filter rule for a predefined SQL query.
 * The generated rule will only return ids that are returned from this query.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 */
class CustomSql extends Simple
{
	/**
	 * Generates the filter rules based upon the given filter url.
	 *
	 * @param IFilter        $objFilter    The filter to append the rules to.
	 *
	 * @param string[string] $arrFilterUrl The parameters to evaluate.
	 *
	 * @return void
	 */
	public function prepareRules(IFilter $objFilter, $arrFilterUrl)
	{
		$arrParams = array();
		$strSql    = $this->generateSql($arrParams, $arrFilterUrl);

		if (!strlen($strSql))
		{
			return;
		}

		$objFilterRule = new SimpleQuery($strSql, $arrParams);
		$objFilter->addFilterRule($objFilterRule);
	}

	/**
	 * Build the SQL query string.
	 *
	 * @param array            $arrParams    Query param stack.
	 *
	 * @param mixed|array|null $arrFilterUrl The filter params (should be array or null).
	 *
	 * @return string
	 */
	protected function generateSql(array &$arrParams, $arrFilterUrl)
	{
		$strSQL = $this->get('customsql');

		$strSQL = $this->parseTable($strSQL);
		$strSQL = $this->parseRequestVars($strSQL, $arrParams, $arrFilterUrl);
		$strSQL = $this->parseSecureInsertTags($strSQL, $arrParams);
		$strSQL = $this->parseInsertTags($strSQL, $arrParams);

		return $strSQL;
	}

	/**
	 * Replace the table name in the query string.
	 *
	 * @param string $strSQL SQL to parse.
	 *
	 * @return string Parsed SQL.
	 */
	protected function parseTable($strSQL)
	{
		return str_replace('{{table}}', $this->getMetaModel()->getTableName(), $strSQL);
	}

	/**
	 * Parse a request var insert tag within the SQL.
	 *
	 * @param string           $strSQL       SQL to parse.
	 *
	 * @param array            $arrParams    Query param stack.
	 *
	 * @param mixed|array|null $arrFilterUrl The filter params (should be array or null).
	 *
	 * @return string Parsed SQL.
	 */
	protected function parseRequestVars($strSQL, array &$arrParams, $arrFilterUrl)
	{
		return preg_replace_callback(
			'@\{\{param::([^}]*)\}\}@',
			function($arrMatch) use(&$arrParams, $arrFilterUrl)
			{
				list($strSource, $strQuery) = explode('?', $arrMatch[1], 2);
				parse_str($strQuery, $arrArgs);
				$arrName = (array)$arrArgs['name'];

				$var = null;

				switch($strSource)
				{
					case 'get':
						$var = \Input::getInstance()->get(array_shift($arrName));
						break;

					case 'post':
						$var = \Input::getInstance()->post(array_shift($arrName));
						break;

					case 'session':
						$var = \Session::getInstance()->get(array_shift($arrName));
						break;

					case 'filter':
						if ($arrFilterUrl)
						{
							$var = $arrFilterUrl[array_shift($arrName)];
						}
						break;

					default:
						// This should never occur.
						return 'NULL';
				}

				$i     = 0;
				$count = count($arrName);
				while ($i < $count && is_array($var))
				{
					$var = $var[$arrName[$i++]];
				}

				if ($i != count($arrName) || $var === null)
				{
					if (isset($arrArgs['default']))
					{
						$arrParams[] = $arrArgs['default'];
						return '?';
					}
					else
					{
						return 'NULL';
					}
				}

				// Treat as scalar value.
				if (empty($arrArgs['aggregate']))
				{
					$arrParams[] = $var;
					return '?';
				}

				// Treat as list.
				$var = (array)$var;

				if (!empty($arrArgs['recursive']))
				{
					$var = iterator_to_array(
						new \RecursiveIteratorIterator(
							new \RecursiveArrayIterator(
								$var
							)
						)
					);
				}

				if (!$var)
				{
					return 'NULL';
				}

				if ($arrArgs['key'])
				{
					$var = array_keys($var);
				}
				else
				{
					// Use values.
					$var = array_values($var);
				}

				if ($arrArgs['aggregate'] == 'set')
				{
					$arrParams[] = implode(',', $var);
					return '?';
				}
				else
				{
					$arrParams = array_merge($arrParams, $var);
					return rtrim(str_repeat('?,', count($var)), ',');
				}
			},
			$strSQL
		);
	}

	/**
	 * Replace all secure insert tags.
	 *
	 * @param string $strSQL    SQL to parse.
	 *
	 * @param array  $arrParams Query param stack.
	 *
	 * @return string Parsed SQL.
	 */
	protected function parseSecureInsertTags($strSQL, array &$arrParams)
	{
		$objMe = $this;
		return preg_replace_callback(
			'@\{\{secure::([^}]+)\}\}@',
			function($arrMatch) use(&$arrParams, $objMe)
			{
				$arrParams[] = $objMe->parseInsertTags('{{' . $arrMatch[1] . '}}', $arrParams);
				return '?';
			},
			$strSQL
		);
	}

	/**
	 * Replace all insert tags in the query string.
	 *
	 * @param string $strSQL    SQL to parse.
	 *
	 * @param array  $arrParams Query param stack.
	 *
	 * @return string Parsed SQL
	 */
	protected function parseInsertTags($strSQL, array &$arrParams)
	{
		return ContaoController::getInstance()->replaceInsertTags($strSQL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParameters()
	{
		$arrParams = array();

		preg_match_all('@\{\{param::filter\?([^}]*)\}\}@', $this->get('customsql'), $arrMatches);
		foreach ($arrMatches[1] as $strQuery)
		{
			parse_str($strQuery, $arrArgs);
			if (isset($arrArgs['name']))
			{
				$arrName     = (array)$arrArgs['name'];
				$arrParams[] = $arrName[0];
			}
		}

		return $arrParams;
	}
}
