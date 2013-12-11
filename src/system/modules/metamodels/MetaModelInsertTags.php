<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package     MetaModels
 * @subpackage  metamodels_inserttags
 * @author      Tim Gatzky <info@tim-gatzky.de>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

/*
Available inserttags

{{metamodel::total::list::ce::*}}
{{metamodel::total::list::mod::*}}
{{metamodel::total::filter::ce::*}}
{{metamodel::total::filter::mod::*}}
--> no need to tell the inserttag its a list or filter type
{{metamodel::total::mod::*}}
{{metamodel::total::ce::*}}

-- item based
{{metamodelitem::table-or-id::item-or-setOfIds::rendersetting}} -> html output
{{metamodeldetailitem::table-or-id::item::rendersetting}} -> html output

-- attribute based
{{metamodelattribute::table-or-id::item-id::field}} -> value, plain text

*/


class MetaModelInsertTags extends Controller
{
	/**
	 * Replace MetaModels related inserttags
	 *
	 * @param string
	 * @return mixed
	 */
	public function replaceTags($strTag)
	{
		$arrElements = explode('::', $strTag);
		
		switch($arrElements[0])
		{
			//-- metamodel
			case 'metamodel':
				
				// toggle OUTPUT-type
				switch($arrElements[1])
				{
					// total count
					case 'total':
						switch($arrElements[2])
						{
							// from module, can be a metamodel list or filter
							case 'mod':
								$objDB = Database::getInstance();
								
								$objResult = $objDB->prepare("SELECT metamodel,metamodel_filtering FROM tl_module WHERE id=?")->limit(1)->execute($arrElements[3]);
								if($objResult->numRows < 1)	{return false;}
								
								$objMetaModel = MetaModelFactory::byId($objResult->metamodel);
								$objFilter = $objMetaModel->prepareFilter($objResult->metamodel_filtering,$_GET);

								return $objMetaModel->getCount($objFilter);
							break;
							// from content element, can be a metamodel list or filter
							case 'ce':
								$objDB = Database::getInstance();
								
								$objResult = $objDB->prepare("SELECT metamodel,metamodel_filtering FROM tl_content WHERE id=?")->limit(1)->execute($arrElements[3]);
								if($objResult->numRows < 1)	{return false;}
								
								$objMetaModel = MetaModelFactory::byId($objResult->metamodel);
								$objFilter = $objMetaModel->prepareFilter($objResult->metamodel_filtering,$_GET);
								
								return $objMetaModel->getCount($objFilter);
							break;
							
							default:
								return false;
							break;
						}
					break;
					
					default:
						return false;
					break;
				}
			break;
			
			//-- metamodelitem
			case 'metamodelitem':
				
				// metamodel by table or id
				$objMetaModel = null;
				if(is_numeric($arrElements[1]))
				{
					$objMetaModel = MetaModelFactory::byId($arrElements[1]);
				}
				else if(is_string($arrElements[1]))
				{
					$objMetaModel = MetaModelFactory::byTableName($arrElements[1]);
				}
				else {return false;}

				$objMetaModelList = new MetaModelList();
				$objMetaModelList->setMetaModel($objMetaModel->get('id'),$arrElements[3]);
				#$objMetaModelList->setFilterParam(0,array(),$_GET);
				
				// handle a set of ids
				$arrIds = explode(',', $arrElements[2]);
				
				// check publish state of item
				$objDB = Database::getInstance();
				$objAttrCheckPublish = $objDB->prepare("SELECT colname FROM tl_metamodel_attribute WHERE pid=? AND check_publish=1")->limit(1)->execute($objMetaModel->get('id'));
							
				if($objAttrCheckPublish->numRows > 0)
				{
					foreach($arrIds as $i => $itemId)
					{
						$objItem = $objMetaModel->findById($itemId);
						if(!$objItem->get($objAttrCheckPublish->colname))
						{
							unset($arrIds[$i]);
						}
					}
				}
				
				// render an empty inserttag rather than displaying a list with an empty result information. do not return false here because the inserttag itself is correct.
				if(count($arrIds) < 1)
				{
					return '';
				}
				
				$objMetaModelList->addFilterRule(new MetaModelFilterRuleStaticIdList($arrIds));
				
				return $objMetaModelList->render(false,$this);
			break;
			
			//-- metamodelattribute
			case 'metamodelattribute':
				
				// metamodel by table or id
				$objMetaModel = null;
				if(is_numeric($arrElements[1]))
				{
					$objMetaModel = MetaModelFactory::byId($arrElements[1]);
				}
				else if(is_string($arrElements[1]))
				{
					$objMetaModel = MetaModelFactory::byTableName($arrElements[1]);
				}
				else {return false;}
				
				// get item
				$objMetaModelItem = $objMetaModel->findById($arrElements[2]);
				
				// parse attribute
				$arrAttr = $objMetaModelItem->parseAttribute($arrElements[3]);
				
				return $arrAttr['text'];
			break;
			
			default:
				return false;
			break;		
		}
	}
}