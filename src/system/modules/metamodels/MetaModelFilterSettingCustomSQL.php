<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This filter condition generates a filter rule for a predefined SQL query.
 * The generated rule will only return ids that are returned from this query.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 */
class MetaModelFilterSettingCustomSQL extends MetaModelFilterSetting
{

	/* (non-PHPdoc)
	 * @see IMetaModelFilterSetting::prepareRules()
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$strSQL = $this->get('customsql');
		$arrParams = array();

		$strSQL = $this->parseTable($strSQL, $arrParams);
		$strSQL = $this->parseRequestVars($strSQL, $arrParams, $arrFilterUrl);
		$strSQL = $this->parseSecureInsertTags($strSQL, $arrParams);
		$strSQL = $this->parseInsertTags($strSQL, $arrParams);

		if (!strlen($strSQL))
		{
			return;
		}

		$objFilterRule = new MetaModelFilterRuleSimpleQuery($strSQL, $arrParams);
		$objFilter->addFilterRule($objFilterRule);
	}

	/**
	 * Replace the table name in the query string.
	 *
	 * @param string $strSQL    SQL to parse.
	 *
	 * @param array  $arrParams Query param stack.
	 *
	 * @return string Parsed SQL.
	 */
	protected function parseTable($strSQL, array &$arrParams)
	{
		return str_replace('{{table}}', $this->getMetaModel()->getTableName(), $strSQL);
	}

	/**
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
				$arrName = (array) $arrArgs['name'];

				$var = null;

				switch($strSource)
				{
					case 'get':
						$var = Input::getInstance()->get(array_shift($arrName));
						break;

					case 'post':
						$var = Input::getInstance()->post(array_shift($arrName));
						break;

					case 'session':
						$var = Session::getInstance()->get(array_shift($arrName));
						break;

					case 'filter':
						if ($arrFilterUrl)
						{
							$var = $arrFilterUrl[array_shift($arrName)];
						}
						break;

					default:
						/* This should never occur. */
						return 'NULL';
						break;
				}

				$i = 0;
				while($i < count($arrName) && is_array($var))
				{
					$var = $var[$arrName[$i++]];
				}

				if($i != count($arrName) || $var === null)
				{
					if(isset($arrArgs['default']))
					{
						$arrParams[] = $arrArgs['default'];
						return '?';
					} else {
						return 'NULL';
					}
				}

				// treat as scalar value
				if(!$arrArgs['aggregate'])
				{
					$arrParams[] = $var;
					return '?';
				}

				// treat as list
				$var = (array) $var;

				if($arrArgs['recursive'])
				{
					$var = iterator_to_array(
						new RecursiveIteratorIterator(
							new RecursiveArrayIterator(
								$var
							)
						)
					);
				}

				if(!$var)
				{
					return 'NULL';
				}

				if($arrArgs['key'])
				{
					$var = array_keys($var);
				} else { // use values
					$var = array_values($var);
				}

				if($arrArgs['aggregate'] == 'set')
				{
					$arrParams[] = implode(',', $var);
					return '?';
				} else {
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
		$objCtrl = MetaModelController::getInstance();
		return preg_replace_callback(
			'@\{\{secure::([^}]+)\}\}@',
			function($arrMatch) use(&$arrParams, $objCtrl)
			{
				$arrParams[] = $objCtrl->replaceInsertTags('{{' . $arrMatch[1] . '}}');
				return '?';
			},
			$strSQL
		);
	}

	/**
	 * Replace all insert tags in the query string.
	 *
	 * @param string $strSQL    SQL to parse
	 *
	 * @param array  $arrParams Query param stack
	 *
	 * @return string Parsed SQL
	 */
	protected function parseInsertTags($strSQL, array &$arrParams)
	{
		return MetaModelController::getInstance()->replaceInsertTags($strSQL);
	}

	/* (non-PHPdoc)
	 * @see MetaModelFilterSetting::getParameters()
	 */
	public function getParameters()
	{
		preg_match_all('@\{\{param::filter\?([^}]*)\}\}@', $this->get('customsql'), $arrMatches);
		foreach($arrMatches[1] as $strQuery) {
			parse_str($strQuery, $arrArgs);
			if(isset($arrArgs['name'])) {
				$arrName = (array) $arrArgs['name'];
				$arrParams[] = $arrName[0];
			}
		}
		return (array) $arrParams;
	}

}

