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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype']         = array('Type', 'Select the attribute type.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id']         = array('Attribute', 'Attribute this setting relates to.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template']        = array('Custom Template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template files start with &quot;mm_<type>&quot; where the type name is put for &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class']        = array('Backend class', 'Here you can select one of the backend class.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['title_legend']    = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['advanced_legend'] = 'Advanced';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new']             = array('New', 'Create new setting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit']            = array('Edit setting', 'Edit setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy']            = array('Copy setting definiton', 'Copy setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete']          = array('Delete setting', 'Delete setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show']            = array('Setting details', 'Show details of setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall']          = array('Add all', 'Add all attributes to palette');

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['row']             = '%s <strong>%s</strong> <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legend_row']      = '<div class="dca_palette">%stest....</div>';

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['legend']         = 'Legend';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['attribute']      = 'Attribute';

?>