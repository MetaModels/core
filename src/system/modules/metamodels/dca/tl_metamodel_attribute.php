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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}


$this->loadLanguageFile('languages');

/**
 * Table tl_metamodel_attribute 
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute'] = array
(
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_metamodel',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'onload_callback'             => array
		(
			array('TableMetaModelAttribute', 'onLoadCallback')
		),
		'onsubmit_callback'           => array
		(
			array('TableMetaModelAttribute', 'onSubmitCallback')
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter,limit', 
			'headerFields'            => array('name', 'tableName', 'tstamp', 'translated', 'supvariants', 'varsupport'), 
			'flag'                    => 1,
			'child_record_callback'   => array('TableMetaModelAttribute', 'renderField') 
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s'
		),

		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

		)
	),

	'metapalettes' => array
	(
		// initial palette with only the type to be selected.
		'default' => array
		(
			'title' => array('type')
		),

		// base palette for MetaModelAttribute derived types
		'_base_ extends default' => array
		(
			'+title'			=> array('colname', 'name', 'description'),
			'advanced'			=> array(':hide', 'isvariant', 'mandatory', 'unique', 'hasdefault'),
			'metamodeloverview'	=> array('sortingField', 'filteredField', 'searchableField'),
			'backenddisplay'	=> array('titleField', 'width50', 'insertBreak'),
		),

		// default palette for MetaModelAttributeSimple derived types
		'_simpleattribute_ extends _base_' => array
		(
//			'+title' => array('')
		),

		// default palette for MetaModelAttributeComplex derived types
		'_complexattribute_ extends _base_' => array
		(
//			'+title' => array('')
		),
	),

/*
			'{display_legend},parentCheckbox,titleField,width50;'.
			'{legend_legend:hide},insertBreak;'.
			'{filter_legend:hide},sortingField,filteredField,searchableField;'.
			'{advanced_legend:hide},mandatory,defValue,uniqueItem;'.
			'{format_legend:hide},formatPrePost,format;'.
			'{feedit_legend},editGroups',
*/
	// Subpalettes
	'metasubpalettes' => array
	(
		// displaying in backend
		'insertBreak'		=> array('legendTitle','legendHide'),
		
		'sortingField'		=> 'groupingMode',
		'showImage'				=> 'imageSize',
		'format'					=> 'formatFunction,formatStr',
		'limitItems'			=> 'items,childrenSelMode,parentFilter',
		'customFiletree'	=> 'uploadFolder,validFileTypes,filesOnly',
		'editGroups'			=> 'editGroups',
		'rte'							=> 'rte_editor',
		'multiple'				=> 'sortBy',
	),


	// Palettes
	'palettes' => array
	(
		'__selector__' => array('type')
//		'__selector__' => array('type', 'insertBreak', 'sortingField', 'showImage', 'format', 'limitItems', 'customFiletree', 'editGroups', 'rte', 'multiple'),
//		'default'      => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{advanced_legend:hide},mandatory,defValue,uniqueItem;{format_legend:hide},formatPrePost,format;{feedit_legend},editGroups',
//		'default'      => '{title_legend},type;',
/*
		'longtext'     => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox;{legend_legend:hide},insertBreak;{filter_legend:hide},searchableField;{advanced_legend:hide},mandatory,allowHtml,textHeight,rte;{feedit_legend},editGroups',
		'number'       => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{advanced_legend:hide},mandatory,defValue,minValue,maxValue;{format_legend:hide},formatPrePost,format;{feedit_legend},editGroups',
		'decimal'      => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{advanced_legend:hide},mandatory,defValue,minValue,maxValue;{format_legend:hide},formatPrePost,format;{feedit_legend},editGroups',
		'date'         => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{advanced_legend:hide},mandatory,defValue,includeTime;{format_legend:hide},formatPrePost,format;{feedit_legend},editGroups',
		'select'       => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField;{advanced_legend:hide},mandatory,includeBlankOption;{options_legend},itemTable,itemTableValueCol,itemSortCol,itemFilter,limitItems,treeMinLevel,treeMaxLevel;{feedit_legend},editGroups',
		'tags'         => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},searchableField;{advanced_legend:hide},mandatory;{options_legend},itemTable,itemTableValueCol,itemSortCol,itemFilter,limitItems,treeMinLevel,treeMaxLevel;{feedit_legend},editGroups',
		'checkbox'     => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField;{feedit_legend},editGroups',
		'url'          => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{advanced_legend:hide},mandatory,uniqueItem,allowedHosts;{format_legend:hide},formatPrePost,{feedit_legend},editGroups',
		'file'         => '{title_legend},name,description,colname,type;{display_legend},parentCheckbox,titleField;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{advanced_legend:hide},mandatory,customFiletree,multiple;{format_legend},showImage,showLink;{feedit_legend},editGroups',
		'calc'         => '{title_legend},name,description,colname,type,calcValue;{display_legend},parentCheckbox,titleField,width50;{legend_legend:hide},insertBreak;{filter_legend:hide},sortingField,filteredField,searchableField;{format_legend:hide},formatPrePost,format;{feedit_legend},editGroups',

 */
	),
/*
	// Subpalettes
	'subpalettes' => array
	(
		'insertBreak'			=> 'legendTitle,legendHide',
		'sortingField'		=> 'groupingMode',
		'showImage'				=> 'imageSize',
		'format'					=> 'formatFunction,formatStr',
		'limitItems'			=> 'items,childrenSelMode,parentFilter',
		'customFiletree'	=> 'uploadFolder,validFileTypes,filesOnly',
		'editGroups'			=> 'editGroups',
		'rte'							=> 'rte_editor',
		'multiple'				=> 'sortBy',
	),
*/
	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions'],
			'eval'                    => array
			(
				'includeBlankOption' => true,
				'doNotSaveEmpty' => true,
				'alwaysSave' => true,
				'submitOnChange'=> true,
				'tl_class'=>'w50',
				'chosen' => 'true'
			),
			'options_callback'        => array('TableMetaModelAttribute', 'fieldTypesCallback'),
			'save_callback'           => array
			(
				//added by thyon
//				array('tl_metamodel_attribute', 'checkAliasDuplicate'),
//				array('MetaModelDatabase', 'changeColumn')
			)
		),
/*
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
*/
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'],
			'exclude'                 => true,
		),


		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'],
			'exclude'                 => true,
		),

		// AVOID: doNotCopy => true, as child records won't be copied when copy metamodel
		'colname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'=>true,
				'maxlength'=>64,
				'tl_class'=>'w50'
			),
		),

		'isvariant' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),







		'insertBreak' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['insertBreak'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		
		'legendTitle' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['legendTitle'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50')
		),

		'legendHide' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['legendHide'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12')
		),		

		'width50' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['width50'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
		),		

		'titleField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['titleField'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50')
		),		

		'filteredField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['filteredField'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50')
		),
		
		'searchableField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['searchableField'],
			'inputType'               => 'checkbox',
		),
		
		'sortingField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['sortingField'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		
		'groupingMode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['groupingMode'],
			'inputType'               => 'select',
			'options'                 => range(0, 12),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['groupingModeOptions'],
			'eval'      							=> array('mandatory' => true, 'includeBlankOption' => true),
		),
		
		'parentCheckbox' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['parentCheckbox'],
			'inputType'               => 'select',
			'options_callback'        => array('tl_metamodel_attribute', 'getCheckboxSelectors'),
			'eval'                    => array('includeBlankOption' => true),
		),
		
		'mandatory' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['mandatory'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
		),

		'includeBlankOption' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['includeBlankOption'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
		),
		
		'defValue' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['defValue'],
			'inputType'               => 'text',
			'eval'                    => array('tl_class'=>'w50'),
		),
		
		'calcValue' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['calcValue'],
			'inputType'               => 'textarea',
			'eval'                    => array('decodeEntities'=>true, 'style'=>'height:80px;', 'mandatory'=>true, 'tl_class'=>'long clr'),
			'save_callback'           => array
			(
//				array('tl_metamodel_attribute', 'checkCalc')
			),
		),

		'unique' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['uniqueItem'],
			'inputType'               => 'checkbox'
		),

		'allowedHosts' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['allowedHosts'],
			'inputType'               => 'listWizard',
			'save_callback'           => array
			(
//				array('tl_metamodel_attribute', 'saveAllowedHosts')
			),
		),
		
		'minValue' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['minValue'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp' => 'digit', 'tl_class'=>'w50'),
			'save_callback'           => array
										(
//											array('tl_metamodel_attribute', 'resetMinMaxValues')
										)
		),
		
		'maxValue' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['maxValue'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp' => 'digit', 'tl_class'=>'w50'),
			'save_callback'           => array
										(
//											array('tl_metamodel_attribute', 'resetMinMaxValues')
										)
		),
		
		'format' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['format'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		
		'formatFunction' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['formatFunction'],
			'inputType'               => 'select',
			'options'                 => array('string', 'number', 'date'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['formatFunctionOptions'],
			'eval'                    => array('tl_class'=>'w50'),
		),
		
		'formatStr' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['formatStr'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50')
		),
						
		'formatPrePost' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['formatPrePost'],
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'allowHtml'=>true),
		),

		
		'rte' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['rte'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'rte_editor' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['rte_editor'],
			'inputType'               => 'select',
			'default'				  => 'tinyMCE',
//			'options_callback'        => array('tl_metamodel_attribute', 'getRichTextEditors'),
		),
		
		'allowHtml' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['allowHtml'],
			'inputType'               => 'checkbox'
		),

		'textHeight' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['textHeight'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'rgxp' => 'digit')
		),
		
		'itemTable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['itemTable'],
			'inputType'               => 'select',
//			'options_callback'        => array('tl_metamodel_attribute', 'getTables'),
			'eval'                    => array('submitOnChange'=>true)
		),
		
		'itemTableValueCol' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['itemTableValueCol'],
			'inputType'               => 'select',
//			'options_callback'        => array('tl_metamodel_attribute', 'getTableFields'),
			'eval'                    => array('tl_class'=>'w50', 'submitOnChange'=>true)
		),

		'itemSortCol' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['itemSortCol'],
			'inputType'               => 'select',
//			'options_callback'        => array('tl_metamodel_attribute', 'getTableFields'),
			'eval'                    => array('includeBlankOption'=>true)
		),

		
		'limitItems' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['limitItems'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
		),
		
		'items' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['items'],
			'inputType'               => 'tableTree',
			'eval'                    => array('fieldType'=>'checkbox', 'children'=>true),
			'load_callback'           => array(
//					array('tl_metamodel_attribute', 'onLoadItems')
			),
		),
		
		'childrenSelMode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['childrenSelMode'],
			'inputType'               => 'select',
			'default'               	=> 'treeAll',
			'options'                 => array('items', 'children', 'treeAll', 'treeChildrenOnly'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['childOptions'],
			'eval'                    => array('tl_class'=>'w50'),
		),

		'parentFilter' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['parentFilter'],
			'inputType'               => 'select',
//			'options_callback'        => array('tl_metamodel_attribute', 'getOptionSelectors'),
			'eval'                    => array('includeBlankOption' => true, 'tl_class'=>'w50'),
		),

		'treeMinLevel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['treeMinLevel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => '0',
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50')
		),
		'treeMaxLevel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['treeMaxLevel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => '0',
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50')
		),


		'itemFilter' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['itemFilter'],
			'inputType'               => 'textarea',
			'eval'                    => array('decodeEntities'=>true, 'style'=>'height:80px;')
		),

		
		'includeTime' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['includeTime'],
			'inputType'               => 'checkbox'
		),
				
		'multiple' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['multiple'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr')
		),

		'sortBy' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['sortBy'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('name_asc', 'name_desc', 'date_asc', 'date_desc', 'meta', 'random'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute'],
			'eval'                    => array('tl_class'=>'w50')
		),
		
		'showLink' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['showLink'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12')
		),
		
		'showImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['showImage'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true) 
		),
						
		'imageSize' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['imageSize'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => array('crop', 'proportional', 'box'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50')
		),
		
		'customFiletree' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['customFiletree'],
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr')
		),
		'uploadFolder' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['uploadFolder'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'clr')
		),
		'validFileTypes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['validFileTypes'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50')
		),
		'filesOnly' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['filesOnly'],
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12')
		),
		'editGroups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['editGroups'],
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('title' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['useridfield'], 'multiple'=>true , 'tl_class'=>'w50 m12') // class m12, see #1627
		),
		
	)
);

?>