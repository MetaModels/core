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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Becker <tim@westwerk.ac>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

// Preserve values by extensions but insert as first entry after 'system'.
$arrOld = isset($GLOBALS['BE_MOD']['metamodels']) ? $GLOBALS['BE_MOD']['metamodels'] : array();
unset($GLOBALS['BE_MOD']['metamodels']);
array_insert(
    $GLOBALS['BE_MOD'],
    (array_search('accounts', array_keys($GLOBALS['BE_MOD'])) + 1),
    array
    (
        'metamodels' => array_replace_recursive(
            array
            (
                'metamodels' => array
                (
                    'tables' => array
                    (
                        'tl_metamodel',
                        'tl_metamodel_attribute',
                        'tl_metamodel_filter',
                        'tl_metamodel_filtersetting',
                        'tl_metamodel_rendersettings',
                        'tl_metamodel_rendersetting',
                        'tl_metamodel_dca_sortgroup',
                        'tl_metamodel_dca',
                        'tl_metamodel_dcasetting',
                        'tl_metamodel_dca_combine',
                        'tl_metamodel_dcasetting_condition',
                        'tl_metamodel_searchable_pages'
                    ),
                    'icon'                  => 'system/modules/metamodels/assets/images/backend/logo.png',
                    'callback'              => 'MetaModels\BackendIntegration\Module'
                ),
                'support_metamodels' => array
                (
                    'icon'                  => 'system/modules/metamodels/assets/images/backend/support.png',
                    'callback'              => 'MetaModels\BackendIntegration\Support'
                )
            ),
            // Append all previous data here.
            $arrOld
        )
    )
);

$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionor']['nestingAllowed']                   = true;
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionand']['nestingAllowed']                  = true;
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvalueis']['nestingAllowed']      = false;
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvalueis']['attributes'][]        = 'select';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvalueis']['attributes'][]        =
    'translatedselect';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvalueis']['attributes'][]        = 'checkbox';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvalueis']['attributes'][]        =
    'translatedcheckbox';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertycontainanyof']['nestingAllowed'] = false;
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertycontainanyof']['attributes'][]   = 'tags';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertycontainanyof']['attributes'][]   =
    'translatedtags';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvisible']['nestingAllowed']      = false;
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvisible']['attributes'][]        = 'checkbox';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionpropertyvisible']['attributes'][]        =
    'translatedcheckbox';
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionnot']['nestingAllowed']                  = true;
$GLOBALS['METAMODELS']['inputscreen_conditions']['conditionnot']['maxChildren']                     = 1;

/*
    All system columns that always are defined in a MetaModel table and are not attributes.
    When you alter this, consider to also change @link{MetaModelTableManipulation::STATEMENT_CREATE_TABLE}.
    Extensions will have to alter the table on their own as the columns will not get transported then.
*/

$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'id';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'pid';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'sorting';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'tstamp';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'vargroup';
$GLOBALS['METAMODELS_SYSTEM_COLUMNS'][] = 'varbase';

// Front-end modules.
$GLOBALS['FE_MOD']['metamodels']['metamodel_list']              = 'MetaModels\FrontendIntegration\Module\ModelList';
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendfilter']   = 'MetaModels\FrontendIntegration\Module\Filter';
$GLOBALS['FE_MOD']['metamodels']['metamodels_frontendclearall'] =
    'MetaModels\FrontendIntegration\Module\FilterClearAll';

// Content elements.
$GLOBALS['TL_CTE']['metamodels']['metamodel_content']           = 'MetaModels\FrontendIntegration\Content\ModelList';
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendfilter']   = 'MetaModels\FrontendIntegration\Content\Filter';
$GLOBALS['TL_CTE']['metamodels']['metamodels_frontendclearall'] =
    'MetaModels\FrontendIntegration\Content\FilterClearAll';

// Frontend widgets.
$GLOBALS['TL_FFL']['multitext'] = 'MetaModels\Widgets\MultiTextWidget';
$GLOBALS['TL_FFL']['tags']      = 'MetaModels\Widgets\TagsWidget';

// HOOKS.
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] =
    array('MetaModels\FrontendIntegration\FrontendFilter', 'generateClearAll');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]      = array('MetaModels\FrontendIntegration\InsertTags', 'replaceTags');

$GLOBALS['TL_PURGE']['folders']['metamodels']['affected'][] = 'system/cache/metamodels';
$GLOBALS['TL_PURGE']['folders']['metamodels']['callback']   =
    array('MetaModels\BackendIntegration\PurgeCache', 'purge');

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

array_insert($GLOBALS['BE_FFL'], 15, array
(
    'mm_subdca'    => 'MetaModels\Widgets\SubDcaWidget'
));

// Selectable styles in the palette tl_class definitions.
$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['w50'],
    'cssclass' => 'w50',
    'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['w50x'],
    'cssclass' => 'w50x',
    'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['clr'],
    'cssclass' => 'clr',
    'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['clx'],
    'cssclass' => 'clx',
    'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['long'],
    'cssclass' => 'long',
    'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['wizard'],
    'cssclass' => 'wizard',
    'image' => ''
);

$GLOBALS['PALETTE_STYLE_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['tl_class']['m12'],
    'cssclass' => 'm12',
    'image' => ''
);

// Selectable panels in the palette panelLayout definitions.
$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['search'],
    'cssclass' => 'search',
    'image' => ''
);

$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['sort'],
    'cssclass' => 'sort',
    'image' => ''
);

$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['filter'],
    'cssclass' => 'filter',
    'image' => ''
);

$GLOBALS['PALETTE_PANEL_PICKER'][] = array
(
    'label' => &$GLOBALS['TL_LANG']['MSC']['panelLayout']['limit'],
    'cssclass' => 'limit',
    'image' => ''
);

// Initialize the filter parameters to an empty array if not initialized yet.
if (!isset($GLOBALS['MM_FILTER_PARAMS'])) {
    $GLOBALS['MM_FILTER_PARAMS'] = array();
}

$GLOBALS['TL_HOOKS']['initializeDependencyContainer'][] = function (
    \Pimple $container
) {
    $handler = new MetaModels\Helper\SubSystemBoot();
    $handler->boot($container);
};
