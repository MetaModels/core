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

$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner_legend'] = 'Combination configuration';

$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner'][0] = 'Permissions for input screen and views';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner'][1] =
    'For selected frontend user group (if any) and selected backend user group (if any) use the selected palette and ' .
    'the selected view.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group'][0]     = 'Frontend group';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group'][1]     =
    'The frontend user group the combination applies to; * is \'catch all\'.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group'][0]     = 'Backend group';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group'][1]     =
    'The backend user group the combination applies to; * is \'catch all\'.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id'][0]       = 'The input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id'][1]       = 'The input screen the combination applies to.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id'][0]      = 'The render setting';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id'][1]      = 'The view the combination applies to.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['sysadmin']        = 'Administrator';
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['anonymous']       = 'Anonymous';
