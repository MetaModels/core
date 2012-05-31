<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * The Catalog extension allows the creation of multiple catalogs of custom items,
 * each with its own unique set of selectable field types, with field extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each catalog.
 *
 * PHP version 5
 * @copyright	CyberSpectrum and others, see CONTRIBUTORS
 * @author		Christian Schiffler <c.schiffler@cyberspectrum.de> and others, see CONTRIBUTORS
 * @package		Catalog
 * @license		LGPL
 * @filesource
 */

/**
 * Add palettes to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list']  = '{title_legend},name,headline,type;{config_legend},metamodel,metamodel_use_limit;{template_legend:hide},metamodel_template,metamodel_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'metamodel_use_limit';

// Insert new Subpalettes after position 1
array_insert($GLOBALS['TL_DCA']['tl_module']['subpalettes'], 1, array
	(
		'metamodel_use_limit' => 'metamodel_offset,metamodel_limit',
	)
);

/**
 * Add fields to tl_module
 */

array_insert($GLOBALS['TL_DCA']['tl_module']['fields'] , 1, array
(

	'metamodel' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel'],
		'exclude'                 => true,
		'inputType'               => 'radio',
		'foreignKey'              => 'tl_metamodel.name',
		'eval'                    => array('mandatory'=> true, 'submitOnChange'=> true)
	),

	'metamodel_template' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_template'],
		'default'                 => 'catalog_full',
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('tl_module_catalog','getCatalogTemplates'),
		'eval'                    => array('tl_class'=>'w50')
	),

	'metamodel_layout' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_layout'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('tl_module_catalog', 'getModuleTemplates'),
		'eval'                    => array('tl_class'=>'w50')
	),

	'metamodel_use_limit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval'                    => array('submitOnChange'=> true, 'tl_class' => 'clr'),
	),

	'metamodel_limit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_limit'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'default'               	=> '1',
		'eval'                    => array('rgxp'=>'digit')
	),

	'metamodel_offset' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_offset'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'default'                 => '10',
		'eval'                    => array('rgxp' => 'digit', 'tl_class'=>'w50'),
	)
)); 

/**
 * Class tl_module_catalog
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright	Martin Komara, Thyon Design, CyberSpectrum 2007-2009
 * @author		Martin Komara, 
 * 				John Brand <john.brand@thyon.com>,
 * 				Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Controller
 */
class tl_module_catalog extends Backend
{
	public function getModuleTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('mod_' . $dc->activeRecord->type, $dc->activeRecord->pid);
	}

	public function getCatalogTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('metamodel_', $dc->activeRecord->pid);
	}

	public function getFilterTemplates(DataContainer $dc)
	{
		// fix issue #70 - template selector shall only show relevant templates.
		if (version_compare(VERSION.'.'.BUILD, '2.9.0', '>='))
		{
			return $this->getTemplateGroup('filter_', $dc->activeRecord->pid);
		}
		else
		{
			return $this->getTemplateGroup('filter_');
		}
	}

	public function catalog_edit_default_value_subfields($arrOptions)
	{
		return $GLOBALS['TL_DCA']['tl_module']['fields']['catalog_edit_default_value']['subfields'];
	}

	public function onSaveColumns_catalog_edit_default_value($varValue, DataContainer $dc)
	{
		$result = $this->Database->prepare('SELECT m.* FROM tl_module m WHERE m.id=? AND m.catalog_edit_use_default=1')
								->limit(1)
								->execute($dc->id);
		$fields=deserialize($result->catalog_edit_default);
		$varValue=deserialize($varValue);
		if(is_array($fields))
		{
			foreach($fields as $field)
			{
				$varValue[$field]=(isset($varValue[$field]) ? $varValue[$field] : false);
			}
		}
		$varValue=serialize($varValue);
		return $varValue;
	}

	public function onLoadColumns_catalog_edit_default_value($varValue, DataContainer $dc)
	{
		$result = $this->Database->prepare('SELECT m.* FROM tl_module m WHERE m.id=? AND m.catalog_edit_use_default=1')
								->limit(1)
								->execute($dc->id);
		return $varValue;
	}
}

?>