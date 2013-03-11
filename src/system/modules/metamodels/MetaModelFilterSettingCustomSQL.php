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
		$strSQL = $this->parseInsertTags($strSQL, $arrParams);
		
		if (!strlen($strSQL)) {
			return;
		}
		
		$objFilterRule = new MetaModelFilterRuleSimpleQuery($strSQL, $arrParams);
		$objFilter->addFilterRule($objFilterRule);
	}
	
	/**
	 * @param string $strSQL SQL to parse
	 * @param array $arrParams Query param stack
	 * @return string Parsed SQL
	 */
	protected function parseTable($strSQL, array &$arrParams) {
		return str_replace('{{table}}', $this->getMetaModel()->getTableName(), $strSQL);
	}
	
	/**
	 * @param string $strSQL SQL to parse
	 * @param array $arrParams Query param stack
	 * @param mixed|array|null $arrFilterUrl The filter params (should be array or null)
	 * @throws Exception DOLPHINS RIDING RAINBOWS
	 * @return string Parsed SQL
	 */
	protected function parseRequestVars($strSQL, array &$arrParams, $arrFilterUrl) {
		return preg_replace_callback(
			'@\{\{param'
			. '(?:(?<aggregate>List|Set)(?<key>Key)?(?<recursive>::Recursive)?)?'
			. '::(?<var>get|post|session|filter)'
			. '::(?<name>[^:}]*)'
			. '(?<hasDefault>::(?<default>[^}]*))?'
			. '\}\}@',
			function($arrMatch) use(&$arrParams) {
				$arrName = array_map('urldecode', explode('/', $arrMatch['name']));
				
				switch($arrMatch['var']) {
					case 'get': $var = Input::getInstance()->get(array_shift($arrName)); break;
					case 'post': $var = Input::getInstance()->post(array_shift($arrName)); break;
					case 'session': $var = Session::getInstance()->get(array_shift($arrName)); break;
					case 'filter': $var = $arrFilterUrl ? $arrFilterUrl[array_shift($arrName)] : null; break;
					default: throw new Exception('DOLPHINS RIDING RAINBOWS'); break;
				}
				
				$i = 0;
				while($i < count($arrName) && is_array($var)) {
					$var = $var[$arrName[$i++]];
				}
				if($i != count($arrName) || $var === null) {
					if($arrMatch['hasDefault']) {
						$var = urldecode($arrMatch['default']);
						return '?';
					} else {
						return 'NULL';
					}
				}
				
				// treat as scalar value
				if(!$arrMatch['aggregate']) {
					$arrParams[] = $var;
					return '?';
				}

				// treat as list
				$var = (array) $var;

				if($arrMatch['recursive']) {
					$var = iterator_to_array(
						new RecursiveIteratorIterator(
							new RecursiveArrayIterator(
								$var
							)
						)
					);
				}
				
				if($arrMatch['key']) {
					$var = array_keys($var);
				} else { // use values
					$var = array_values($var);
				}
				
				if(!$var) {
					return 'NULL';
				}
				
				if($arrMatch['aggregate'] == 'set') {
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
	 * @param string $strSQL SQL to parse
	 * @param array $arrParams Query param stack
	 * @return string Parsed SQL
	 */
	protected function parseInsertTags($strSQL, array &$arrParams) {
		return MetaModelController::getInstance()->replaceInsertTags($strSQL);
	}
	
}

