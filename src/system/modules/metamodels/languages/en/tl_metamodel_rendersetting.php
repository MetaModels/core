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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id']         = array('Attribute', 'Attribute this setting relates to.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template']        = array('Custom template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template files start with "mm_&lt;type&gt;" where the type name is put for &lt;type&gt;');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['title_legend']    = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['advanced_legend'] = 'Advanced';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['new']             = array('New', 'Create new setting');

$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit']            = array('Edit setting', 'Edit filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy']            = array('Copy filter setting definition', 'Copy filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete']          = array('Delete filter setting', 'Delete filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show']            = array('Filter setting details', 'Show details of filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall']          = array('Add all', 'Add all attributes to render setting');

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['row']             = '%s <strong>%s</strong> <em>[%s]</em>';

/**
 * Messages
 */

$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_willadd'] = 'Will add attribute %s to rendersetting.';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_alreadycontained'] = 'Attribute %s already in rendersetting.';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_addsuccess'] = 'Added attribute %s to rendersetting.';

