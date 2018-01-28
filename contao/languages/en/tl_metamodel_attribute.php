<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type'][0]                    = 'Attribute type';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type'][1]                    =
    'Select the type of this attribute. ' .
    'WARNING: if you change this, all existing data within this attribute will be deleted.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'][0]                    = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'][1]                    = 'Human readable name';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'][0]             = 'Description';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'][1]             = 'Human readable description';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname'][0]                 = 'Column name';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname'][1]                 =
    'Internal reference name for this attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant'][0]               = 'Enable variant override';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant'][1]               =
    'Check this, if you want variants within the MetaModel to override the parent item\'s value';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique'][0]                = 'Unique values';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique'][1]                =
    'Check this, if you want to ensure that each value only occurs once';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_langcode']              = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_value']                 = 'Description';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['title_legend']               =
    'Type, naming and base attribute configuration';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['advanced_legend']            = 'Advanced settings';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['new'][0]                     = 'New attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['new'][1]                     = 'Create new attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew'][0]                = 'New attribute after this Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew'][1]                = 'New attribute after this Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit'][0]                    = 'Edit attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit'][1]                    = 'Edit attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut'][0]                     = 'Cut attribute definition';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut'][1]                     = 'Cut definition of attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy'][0]                    = 'Copy attribute definition';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy'][1]                    = 'Copy definition of attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete'][0]                  = 'Delete attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete'][1]                  = 'Delete attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show'][0]                    = 'Attribute details';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show'][1]                    = 'Show details of attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['editheader'][0]              = 'Edit attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['editheader'][1]              = 'Edit the attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew'][0]                = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pastenew'][1]                = 'Add new after attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pasteafter'][0]              = 'Create new attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['pasteafter'][1]              = 'Create new after attribute ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['error_unknown_attribute'][0] = 'Unknown attribute!';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['error_unknown_attribute'][1] =
    'Extension missing? The attribute type "%s" is not installed.';
