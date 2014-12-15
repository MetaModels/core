<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christopher Bölter <c.boelter@cogizz.de>
 * @author     Ondrej <Sam256@web.de>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_autocreatepalette'       => 'system/modules/metamodels/templates',
    'be_autocreateview'          => 'system/modules/metamodels/templates',
    'be_dcastylepicker'          => 'system/modules/metamodels/templates',
    'be_detectedproblems'        => 'system/modules/metamodels/templates',
    'be_metamodel_full'          => 'system/modules/metamodels/templates',
    'be_subdca'                  => 'system/modules/metamodels/templates',
    'be_supportscreen'           => 'system/modules/metamodels/templates',
    'ce_metamodel_list'          => 'system/modules/metamodels/templates',
    'metamodel_prerendered'      => 'system/modules/metamodels/templates',
    'metamodel_unrendered'       => 'system/modules/metamodels/templates',
    'mod_metamodel_list'         => 'system/modules/metamodels/templates',
    'mm_filter_default'          => 'system/modules/metamodels/templates',
    'mm_filter_clearall'         => 'system/modules/metamodels/templates',
    'mm_filteritem_default'      => 'system/modules/metamodels/templates',
    'mm_filteritem_linklist'     => 'system/modules/metamodels/templates',
    'mm_filteritem_radiobuttons' => 'system/modules/metamodels/templates',
    'mm_filteritem_checkbox'     => 'system/modules/metamodels/templates',
));
