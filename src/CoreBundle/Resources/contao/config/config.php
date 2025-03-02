<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Becker <tim@westwerk.ac>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use Contao\ArrayUtil;
use Contao\System;
use MetaModels\BackendIntegration\PurgeAssets;
use MetaModels\BackendIntegration\PurgeTranslator;
use MetaModels\CoreBundle\Contao\Hooks\LoadDataContainer;
use MetaModels\FrontendIntegration\Content\Filter;
use MetaModels\FrontendIntegration\Content\FilterClearAll;
use MetaModels\FrontendIntegration\FrontendFilter;
use MetaModels\Widgets\MultiTextWidget;
use MetaModels\Widgets\SubDcaWidget;
use MetaModels\Widgets\TagsWidget;

$container = System::getContainer();

// @deprecated Use the config parameter metamodels.system_columns instead.
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'] = $container->getParameter('metamodels.system_columns');

// Front-end modules.
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendfilter']   = Filter::class;
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendclearall'] = FilterClearAll::class;

// Content elements.
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendfilter']   = Filter::class;
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendclearall'] = FilterClearAll::class;

// Frontend widgets.
$GLOBALS['TL_FFL']['multitext'] = MultiTextWidget::class;
$GLOBALS['TL_FFL']['tags']      = TagsWidget::class;

// HOOKS.
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = [FrontendFilter::class, 'generateClearAll'];

// Add cache only if dir defined in container (and therefore we are using the cache).
if ($cacheDir = $container->getParameter('metamodels.cache_dir')) {
    // We need to translate the cache dir - otherwise the backend view is distorted. See \Contao\PurgeData::run().
    $GLOBALS['TL_PURGE']['folders']['metamodels']['affected'] = [
        str_replace(
            $container->getParameter('kernel.cache_dir') . '/',
            '%s/',
            $cacheDir
        )
    ];
    $GLOBALS['TL_PURGE']['folders']['metamodels']['callback'] = ['metamodels.cache.purger', 'purge'];
}

$GLOBALS['TL_PURGE']['folders']['metamodels_assets']['affected'][] = 'assets/metamodels';
$GLOBALS['TL_PURGE']['folders']['metamodels_assets']['callback']   = [PurgeAssets::class, 'purge'];

$GLOBALS['TL_PURGE']['folders']['translator']['affected'] = [];
$GLOBALS['TL_PURGE']['folders']['translator']['callback'] = [PurgeTranslator::class, 'purge'];

// Meta Information.
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'text';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'select';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'translatedtext';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'translatedselect';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'combinedvalues';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'translatedcombinedvalues';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'text';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'select';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedtext';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedselect';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'longtext';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedlongtext';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedcombinedvalues';

ArrayUtil::arrayInsert($GLOBALS['BE_FFL'], 15, [
    'mm_subdca' => SubDcaWidget::class
]);

// Initialize the filter parameters to an empty array if not initialized yet.
if (!isset($GLOBALS['MM_FILTER_PARAMS'])) {
    $GLOBALS['MM_FILTER_PARAMS'] = [];
}

$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['metamodels.sub_system_boot', 'boot'];

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [LoadDataContainer::class, 'onLoadDataContainer'];
