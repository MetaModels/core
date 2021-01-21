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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['list_label']                = 'Filter settings';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid'][0]                    = 'Parent collection';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid'][1]                    =
    'The collection of filter settings, this setting belongs to.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type'][0]                   = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type'][1]                   = 'The type of this setting.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled'][0]                = 'Enabled';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled'][1]                = 'Enable this filter setting.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['comment'][0]                = 'Comment';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['comment'][1]                =
    'A short comment for describing the purpose of this filter setting.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id'][0]                = 'Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id'][1]                = 'Attribute this setting relates to.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['all_langs'][0]              = 'Search all languages';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['all_langs'][1]              =
    'Check if you want to perform the lookup language independant. ' .
    'If this is not checked, only the current active language will be searched.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['allow_empty'][0]            = 'Allow empty value';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['allow_empty'][1]            =
    'Check if you want to allow this filter value to be emtpy, if checked and the parameter holds an empty value, ' .
    'this filter rule will behave as if it was not defined.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['stop_after_match'][0]       = 'Stop after first match';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['stop_after_match'][1]       =
    'Check if you want this filter setting to stop executing its child rules after the first subset returned matches.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'][0]               = 'URL parameter';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'][1]               =
    'The URL parameter that shall get mapped to the selected attribute. ' .
    'The special <em>"auto_item"</em> parameter can also be used, this is especially useful for alias columns.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['predef_param'][0]           = 'Static parameter';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['predef_param'][1]           =
    'Check if you want to be able to set the value of this parameter in the parenting list ' .
    '(modules, content elements, etc.).';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fe_widget'][0]              = 'Provide Frontend widget';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fe_widget'][1]              =
    'Check if you want to display a filter widget in the Frontend.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'][0]              = 'Custom SQL Query';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'][1]              =
    'The SQL query that shall be executed, insert tags are supported.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['label'][0]                  = 'Label';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['label'][1]                  =
    'Show label instead of attribute name.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['template'][0]               = 'Template';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['template'][1]               =
    'Sub template for this filter element. Standard: form widget.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['blankoption'][0]            = 'Empty option';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['blankoption'][1]            = 'Show empty options in select.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlyused'][0]               = 'Assigned values only';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlyused'][1]               =
    'Show only options, that are assigned somewhere in the MetaModel.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'][0]           = 'Remaining values only';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'][1]           =
    'Show only options, that are still assigned somewhere after the actual filter is set.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['skipfilteroptions'][0]      =
    'Ignore this filter for the remaining values';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['skipfilteroptions'][1]      =
    'If activate the filter will return all options without itself in the filter rules.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['hide_label'][0]             =
    'Hide label in filter widget';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['hide_label'][1]             =
    'If active, the label is not output.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['defaultid'][0]              = 'Default';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['defaultid'][1]              = 'Default value for selection.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['title_legend']              = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['config_legend']             = 'Configuration';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fefilter_legend']           = 'Frontend filter';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['new'][0]                    = 'New';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['new'][1]                    = 'Create new setting.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit'][0]                   = 'Edit setting';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit'][1]                   = 'Edit filter setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy'][0]                   = 'Copy filter setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy'][1]                   = 'Copy filter setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['cut'][0]                    = 'Cut filter setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['cut'][1]                    = 'Cut filter setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete'][0]                 = 'Delete filter setting';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete'][1]                 = 'Delete filter setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show'][0]                   = 'Filter setting details';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show'][1]                   = 'Show details of filter setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['pastenew'][0]               = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['pastenew'][1]               = 'Add new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['pasteafter'][1]             = 'Create new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['pasteinto'][0]              = 'Create new setting at the top';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['pasteinto'][1]              =
    'Create new at the top of setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['idlist']       = 'Predefined set of items';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['simplelookup'] = 'Simple lookup';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['customsql']    = 'Custom SQL';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['conditionor']  = 'OR condition';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['conditionand'] = 'AND condition';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_']     =
    '<span title="%s"><sup>(?)</sup></span>';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_default_']     =
    '%s <strong>%s</strong> %s <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['simplelookup']  =
    '%s <strong>%s</strong> %s <br /> on attribute <em>%s</em> (URL parameter: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionor']   =
    '%s <strong>%s</strong> %s <br /> items that are mentioned in any result.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionand']  =
    '%s <strong>%s</strong> %s <br /> items that are mentioned in all results.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['fefilter']      =
    '%s <strong>%s</strong> %s <br /> for attribute <em>%s</em> (URL parameter: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['items'][0]                  = 'Items';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['items'][1]                  =
    'Please enter the IDs of the items for filtering as comma-separated list.';
