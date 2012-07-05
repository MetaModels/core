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

/**
 * Add palettes to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list']  = '{title_legend},name,headline,type;{config_legend},metamodel,perPage,metamodel_use_limit,metamodel_sortby,metamodel_filtering;{template_legend:hide},metamodel_template,metamodel_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

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
		'options_callback'        => array('tl_module_metamodel','getMetaModelTemplates'),
		'eval'                    => array('tl_class'=>'w50')
	),

	'metamodel_layout' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_layout'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('tl_module_metamodel', 'getModuleTemplates'),
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
		'eval'                    => array('rgxp'=>'digit')
	),

	'metamodel_offset' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_offset'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'eval'                    => array('rgxp' => 'digit', 'tl_class'=>'w50'),
	),

	'metamodel_sortby' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('tl_module_metamodel', 'getAttributeNames'),
		'eval'                    => array('includeBlankOption' => true, 'tl_class'=>'w50'),
	),

	'metamodel_filtering' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'options_callback'        => array('tl_module_metamodel', 'getAttributeNames'),
		'default'                 => '10',
		'eval'                    => array('includeBlankOption' => true, 'tl_class'=>'w50', 'multiple' => true),
	)
));

/**
 * complementary methods needed by the DCA.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
class tl_module_metamodel extends Backend
{
	public function getModuleTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('mod_' . $dc->activeRecord->type, $dc->activeRecord->pid);
	}

	public function getMetaModelTemplates(DataContainer $dc)
	{
		return $this->getTemplateGroup('metamodel_', $dc->activeRecord->pid);
	}

	public function getAttributeNames(DataContainer $dc)
	{
		$arrAttributeNames = array();
		$objMetaModel = MetaModelFactory::byId($dc->activeRecord->metamodel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName();
		}
		return $arrAttributeNames;
	}
}

?>