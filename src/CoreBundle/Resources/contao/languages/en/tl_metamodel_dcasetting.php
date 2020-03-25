<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Fields
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype'][0]                 = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype'][1]                 = 'Select the attribute type.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id'][0]                 = 'Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id'][1]                 = 'Attribute this setting relates to.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template'][0]                = 'Custom template to use for generating';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template'][1]                =
    'Select the template that shall be used for the selected attribute. ' .
    'Valid template files start with "mm_&lt;type&gt;" where the type name is put for &lt;type&gt;';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class'][0]                = 'Backend class';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class'][1]                =
    'Here you can set backend class(es). Open the wizard for an overview of the classes.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide'][0]              = 'Collapse section';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide'][1]              = 'Collapse the section by default.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle'][0]             = 'Legend title';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle'][1]             = 'Here you can enter the legend title.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode']              = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value']                 = 'Legend title';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory'][0]               = 'Mandatory';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory'][1]               = 'Check if this attribute shall be ' .
    'mandatory.
<br />NOTE: This will be implicitely active on if you selected "Unique values" in the attribute configuration.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['alwaysSave'][0]              = 'Always save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['alwaysSave'][1]              =
    'If true the field will always be saved, even if its value has not changed.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['chosen'][0]                  = 'Chosen';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['chosen'][1]                  = 'Enable Chosen graphical select widget.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable'][0]              = 'Filterable';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable'][1]              =
    'Check if this attribute shall be available for backend filtering.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable'][0]              = 'Searchable';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable'][1]              =
    'Check if this attribute shall be available for backend search.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['allowHtml'][0]               = 'Do not encode allowed html tags.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['allowHtml'][1]               =
    'If you select this, allowed HTML tags from system settings will not be encoded.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['preserveTags'][0]            = 'Do not encode all html tags.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['preserveTags'][1]            =
    'If you select this, no HTML tags will be encoded.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['decodeEntities'][0]          = 'Decode HTML entities.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['decodeEntities'][1]          =
    'If you select this, all HTML entities will be decoded. Note that HTML entities are always decoded if "Do not encode allowed html tags" is true.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rte'][0]                     = 'Enable richtext editor on this';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rte'][1]                     =
    'Select the rich text configuration that shall be used on this field (if any).';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rows'][0]                    = 'Rows';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rows'][1]                    =
    'Amount of rows to use for longtext/table widget.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cols'][0]                    = 'Columns';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cols'][1]                    =
    'Amount of colums to use for longtext/table widget';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash'][0]           = 'Trailing slash handling';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash'][1]           =
    'Here you can specify how trailing slashes shall be handled';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['spaceToUnderscore'][0]       = 'Replace spaces with underscore';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['spaceToUnderscore'][1]       =
    'If true any whitespace character will be replaced by an underscore.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['includeBlankOption'][0]      = 'Include blank option';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['includeBlankOption'][1]      =
    'if true a blank option will be added to the options which allows to define a &quot;no item selected&quot; option.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['readonly'][0]                = 'Read only';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['readonly'][1]                =
    'If true a the widget will be read only and may not be changed.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['submitOnChange'][0]          = 'Submit on change';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['submitOnChange'][1]          =
    'If active the form will be submitted when the field value changes.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['title_legend']               = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['presentation_legend']        = 'Widget appearance related options';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['functions_legend']           = 'Functionality related options';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['overview_legend']            = 'Filtering and searching in the backend list';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['backend_legend']             = 'Backend';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['config_legend']              = 'Configuration';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['advanced_legend']            = 'Advanced';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new'][0]                     = 'New';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new'][1]                     = 'Create new setting.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit'][0]                    = 'Edit setting';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit'][1]                    = 'Edit setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cut'][0]                     = 'Cut setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cut'][1]                     = 'Cut setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy'][0]                    = 'Copy setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy'][1]                    = 'Copy setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete'][0]                  = 'Delete setting';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete'][1]                  = 'Delete setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['conditions'][0]              = 'Manage visibility conditions';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['conditions'][1]              =
    'Manage the visibility conditions of property ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show'][0]                    = 'Setting details';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show'][1]                    = 'Show details of setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['toggle'][0]                  = 'Toggle';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['toggle'][1]                  = 'Toggle the state of setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][0]                  = 'Add all';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1]                  = 'Add all attributes to input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['pastenew'][0]                = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['pastenew'][1]                = 'Add new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['pasteafter'][0]              = 'Create new setting at the top';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['pasteafter'][1]              = 'Create new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['legend']         = 'Legend';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['attribute']      = 'Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['0'] = 'Strip slash on save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['1'] = 'Add slash on save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['2'] = 'Do nothing';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd']             =
    'Will add the attribute "%s" to the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained']    =
    'Attribute %s is already contained in input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess']          =
    'Added the attribute "%s" to the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_activate']            = 'Add new settings enabled.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory_for_unique_attr']  =
    'Unique attribues are automatically mandatory (this is not changable).';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['readonly_for_force_alias']  =
    'Attribues with force alias are automatically readonly (this is not changable).';
