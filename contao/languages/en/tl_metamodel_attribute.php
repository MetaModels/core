<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type']                 = array('Attribute type', 'Select the type of this attribute. WARNING: if you change this, all existing data within this attribute will be deleted.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name']                 = array('Name', 'Human readable name');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description']          = array('Description', 'Human readable description');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname']              = array('Column name', 'Internal reference name for this attribute');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant']            = array('Enable variant override', 'Check this, if you want variants within the MetaModel to override the parent item\'s value');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique']             = array('Unique values', 'Check this, if you want to ensure that each value only occurs once');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_langcode']        = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_value']           = 'Description';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['title_legend']         = 'Type, naming and base attribute configuration';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['advanced_legend']      = 'Advanced settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['new']                  = array('New attribute', 'Create new attribute');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew']             = array('New attribute after this Attribute', 'New attribute after this Attribute');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit']                 = array('Edit attribute', 'Edit attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut']                  = array('Cut attribute definition', 'Cut definition of attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy']                 = array('Copy attribute definition', 'Copy definition of attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete']               = array('Delete attribute', 'Delete attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show']                 = array('Attribute details', 'Show details of attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['editheader']           = array('Edit attribute', 'Edit the attribute');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew'][0]          = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew'][1]          = 'Add new after attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pasteafter'][0]        = 'Create new attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pasteafter'][1]        = 'Create new after attribute ID %s';

// Error messages.
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['error_unknown_attribute'][0] = 'Unknown attribute!';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['error_unknown_attribute'][1] =
    'Extension missing? The attribute type "%s" is not installed.';
