<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_LANG']['tl_metamodel']['id'][0]                     = 'Id';
$GLOBALS['TL_LANG']['tl_metamodel']['id'][1]                     = 'Id of the MetaModel';
$GLOBALS['TL_LANG']['tl_metamodel']['name'][0]                   = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel']['name'][1]                   = 'MetaModel name.';
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp'][0]                 = 'Revision date';
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp'][1]                 = 'Date and time of the latest revision';
$GLOBALS['TL_LANG']['tl_metamodel']['tableName'][0]              = 'Table name';
$GLOBALS['TL_LANG']['tl_metamodel']['tableName'][1]              = 'Name of database table to store items to.';
$GLOBALS['TL_LANG']['tl_metamodel']['mode'][0]                   = 'Parent list mode';
$GLOBALS['TL_LANG']['tl_metamodel']['mode'][1]                   = 'Mode to use for parent/child relationship.';
$GLOBALS['TL_LANG']['tl_metamodel']['translated'][0]             = 'Translation';
$GLOBALS['TL_LANG']['tl_metamodel']['translated'][1]             =
    'Check if this MetaModel shall support translation/multilingualism.';
$GLOBALS['TL_LANG']['tl_metamodel']['languages'][0]              = 'Languages to provide for translation';
$GLOBALS['TL_LANG']['tl_metamodel']['languages'][1]              =
    'Specify all languages that shall be available for translation.';
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode'][0]     = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode'][1]     = 'Select the languages you want to provide.';
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'][0]   = 'Fallback language';
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'][1]   = 'Check the language that shall be used as fallback.';
$GLOBALS['TL_LANG']['tl_metamodel']['varsupport'][0]             = 'Variant support';
$GLOBALS['TL_LANG']['tl_metamodel']['varsupport'][1]             =
    'Check if this MetaModel shall support variants of items.';
$GLOBALS['TL_LANG']['tl_metamodel']['localeterritorysupport'][0] = 'Locale territory support';
$GLOBALS['TL_LANG']['tl_metamodel']['localeterritorysupport'][1] =
    'Check if this MetaModel shall support language territory at locale.';
$GLOBALS['TL_LANG']['tl_metamodel']['sorting'][0]                = 'Sorting';
$GLOBALS['TL_LANG']['tl_metamodel']['sorting'][1]                = 'Sorting order of items.';
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']              = 'Name and table';
$GLOBALS['TL_LANG']['tl_metamodel']['translated_legend']         = 'Translation';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']           = 'Advanced settings';
$GLOBALS['TL_LANG']['tl_metamodel']['new'][0]                    = 'New MetaModel';
$GLOBALS['TL_LANG']['tl_metamodel']['new'][1]                    = 'Create a new MetaModel.';
$GLOBALS['TL_LANG']['tl_metamodel']['edit'][0]                   = 'Manage items';
$GLOBALS['TL_LANG']['tl_metamodel']['edit'][1]                   = 'Manage items of MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['copy'][0]                   = 'Copy MetaModel definition';
$GLOBALS['TL_LANG']['tl_metamodel']['copy'][1]                   = 'Copy definition of MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['delete'][0]                 = 'Delete MetaModel';
$GLOBALS['TL_LANG']['tl_metamodel']['delete'][1]                 = 'Delete MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['show'][0]                   = 'MetaModel details';
$GLOBALS['TL_LANG']['tl_metamodel']['show'][1]                   = 'Show details of MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['editheader'][0]             = 'Edit MetaModel';
$GLOBALS['TL_LANG']['tl_metamodel']['editheader'][1]             = 'Edit the MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['fields'][0]                 = 'Define attributes';
$GLOBALS['TL_LANG']['tl_metamodel']['fields'][1]                 = 'Define attributes for MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['filter'][0]                 = 'Define filters';
$GLOBALS['TL_LANG']['tl_metamodel']['filter'][1]                 = 'Define filters for MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings'][0]         = 'Define render settings';
$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings'][1]         = 'Define render settings for MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['dca'][0]                    = 'Define input screens';
$GLOBALS['TL_LANG']['tl_metamodel']['dca'][1]                    = 'Define input screens for MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine'][0]            = 'Define input/output combinations';
$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine'][1]            =
    'Define input/output combinations for MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['cut'][0]                    = 'Move MetaModel';
$GLOBALS['TL_LANG']['tl_metamodel']['cut'][1]                    = 'Define the order of your MetaModels.';
$GLOBALS['TL_LANG']['tl_metamodel']['searchable_pages'][0]       = 'Define search settings';
$GLOBALS['TL_LANG']['tl_metamodel']['searchable_pages'][1]       = 'Define search settings for MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['pastenew'][0]               = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel']['pastenew'][1]               = 'Add new after MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['pasteafter'][0]             = 'Create new MetaModel';
$GLOBALS['TL_LANG']['tl_metamodel']['pasteafter'][1]             = 'Create new after MetaModel ID %s';
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormatCount']['0']      = '%s items';
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormatCount']['1']      = '%s item';
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormatCount']['2:']     = '%s items';
$GLOBALS['TL_LANG']['tl_metamodel']['deleteConfirm']             = 'Do you really want to delete MetaModel ID %s?';

$GLOBALS['TL_LANG']['tl_metamodel']['hint_schema_manager'] =
    'After creating an model, the database must be migrated (console, Contao Manager) -' .
    ' even if the table name is changed. When changing the table name, the user data itself must be transferred.';
