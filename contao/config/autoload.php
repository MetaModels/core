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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christopher Bölter <c.boelter@cogizz.de>
 * @author     Ondrej <Sam256@web.de>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_addallattributes'        => 'system/modules/metamodels/templates',
    'be_dcastylepicker'          => 'system/modules/metamodels/templates',
    'be_detectedproblems'        => 'system/modules/metamodels/templates',
    'be_metamodel_full'          => 'system/modules/metamodels/templates',
    'be_subdca'                  => 'system/modules/metamodels/templates',
    'be_supportscreen'           => 'system/modules/metamodels/templates',
    'ce_metamodel_list'          => 'system/modules/metamodels/templates',
    'metamodel_prerendered'      => 'system/modules/metamodels/templates',
    'metamodel_unrendered'       => 'system/modules/metamodels/templates',
    'mod_metamodel_list'         => 'system/modules/metamodels/templates',
    'mm_actionbutton'            => 'system/modules/metamodels/templates',
    'mm_filter_default'          => 'system/modules/metamodels/templates',
    'mm_filter_clearall'         => 'system/modules/metamodels/templates',
    'mm_filteritem_default'      => 'system/modules/metamodels/templates',
    'mm_filteritem_linklist'     => 'system/modules/metamodels/templates',
    'mm_filteritem_radiobuttons' => 'system/modules/metamodels/templates',
    'mm_filteritem_checkbox'     => 'system/modules/metamodels/templates',
));
