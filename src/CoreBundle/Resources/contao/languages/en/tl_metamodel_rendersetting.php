<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
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
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id'][0]              = 'Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id'][1]              = 'Attribute this setting relates to.';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template'][0]             = 'Custom template to use for generating';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template'][1]             =
    'Select the template that shall be used for the selected attribute. ' .
    'Valid template files start with "mm_&lt;type&gt;" where the type name is put for &lt;type&gt;';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['additional_class'][0]     = 'Custom CSS class';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['additional_class'][1]     =
    'Enter any CSS classes that you want get added to the output of this attribute';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['title_legend']            = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['advanced_legend']         = 'Advanced';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['new'][0]                  = 'New';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['new'][1]                  = 'Create new setting';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit'][0]                 = 'Edit setting';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit'][1]                 = 'Edit render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['cut'][0]                  = 'Cut render setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['cut'][1]                  = 'Cut the render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy'][0]                 = 'Copy render setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy'][1]                 = 'Copy the render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete'][0]               = 'Delete render setting';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete'][1]               = 'Delete the render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show'][0]                 = 'Render setting details';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show'][1]                 = 'Show details of render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['toggle'][0]               = 'Toggle';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['toggle'][1]               =
    'Toggle the state of render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'][0]               = 'Add all';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'][1]               = 'Add all attributes to render setting';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['pastenew'][0]             = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['pastenew'][1]             = 'Add new after render setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['row']                     = '%s <strong>%s</strong> <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_willadd']          =
    'Will add attribute "%s" [%s] to rendersetting.';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_alreadycontained'] =
    'Attribute "%s" [%s] already in rendersetting.';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_addsuccess']       =
    'Added attribute "%s" [%s] to rendersetting.';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_activate']         = 'Add new settings enabled.';

$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['deleteConfirm'] =
    'Do you really want to render filter setting ID %s?';
