<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$container = \Contao\System::getContainer();

// @deprecated Use the config parameter metamodels.system_columns instead.
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'] = $container->getParameter('metamodels.system_columns');

// Front-end modules.
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendfilter']   = 'MetaModels\FrontendIntegration\Module\Filter';
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendclearall'] =
    'MetaModels\FrontendIntegration\Module\FilterClearAll';

// Content elements.
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendfilter']   = 'MetaModels\FrontendIntegration\Content\Filter';
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendclearall'] =
    'MetaModels\FrontendIntegration\Content\FilterClearAll';

// Frontend widgets.
$GLOBALS['TL_FFL']['multitext'] = 'MetaModels\Widgets\MultiTextWidget';
$GLOBALS['TL_FFL']['tags']      = 'MetaModels\Widgets\TagsWidget';

// HOOKS.
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] =
    array('MetaModels\FrontendIntegration\FrontendFilter', 'generateClearAll');

// Add cache only if dir defined in container (and therefore we are using the cache).
if ($cacheDir = $container->getParameter('metamodels.cache_dir')) {
    // We need to translate the cache dir - otherwise the backend view is distorted. See \Contao\PurgeData::run().
    $GLOBALS['TL_PURGE']['folders']['metamodels']['affected'] = [str_replace(
        $container->getParameter('kernel.cache_dir') . '/',
        '%s/',
        $cacheDir
    )];
    $GLOBALS['TL_PURGE']['folders']['metamodels']['callback'] = ['metamodels.cache.purger', 'purge'];
}

$GLOBALS['TL_PURGE']['folders']['metamodels_assets']['affected'][] = 'assets/metamodels';
$GLOBALS['TL_PURGE']['folders']['metamodels_assets']['callback']   =
    array('MetaModels\BackendIntegration\PurgeAssets', 'purge');

// Meta Information.
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'text';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'select';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'translatedtext';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'translatedselect';
$GLOBALS['METAMODELS']['metainformation']['allowedTitle'][]       = 'combinedvalues';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'text';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'select';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedtext';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedselect';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'longtext';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'translatedlongtext';
$GLOBALS['METAMODELS']['metainformation']['allowedDescription'][] = 'combinedvalues';

\Contao\ArrayUtil::arrayInsert($GLOBALS['BE_FFL'], 15, array
(
    'mm_subdca'    => 'MetaModels\Widgets\SubDcaWidget'
));

// Initialize the filter parameters to an empty array if not initialized yet.
if (!isset($GLOBALS['MM_FILTER_PARAMS'])) {
    $GLOBALS['MM_FILTER_PARAMS'] = array();
}

$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['metamodels.sub_system_boot', 'boot'];

$GLOBALS['TL_HOOKS']['loadDataContainer'][] =
    [\MetaModels\CoreBundle\Contao\Hooks\LoadDataContainer::class, 'onLoadDataContainer'];
