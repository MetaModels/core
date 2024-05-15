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

/* custom sql filter setting */
$GLOBALS['TL_LANG']['XPL']['customsql'][0][0] = 'Abstract';
$GLOBALS['TL_LANG']['XPL']['customsql'][0][1] = 'Type in any SQL query that shall be executed.<br />
        It is required that this query returns at least one column named "id".<br />
        It is not possible to pass calculations from the SQL to the list.<br />
        The declaration of column names should be entered with table alias as prefix e.g. t.name.';
$GLOBALS['TL_LANG']['XPL']['customsql'][1][0] = 'Example 1<br />plain query';
$GLOBALS['TL_LANG']['XPL']['customsql'][1][1] = '<pre>SELECT t.id FROM mm_mymetamodel AS t WHERE t.page_id=1</pre>
        This selects all IDs from the table <em>mm_mymetamodel</em> that have the value <em>page_id=1</em>
        ';
$GLOBALS['TL_LANG']['XPL']['customsql'][2][0] = 'Example 2<br />insert tablename';
$GLOBALS['TL_LANG']['XPL']['customsql'][2][1] = '<pre>SELECT t.id FROM {{table}} AS t WHERE t.page_id=1</pre>
        This is merely the same as example 1 but the table name of the current MetaModel
        (i.e.: the <em>mm_mymetamodel</em> from above) will get inserted into the query.';
$GLOBALS['TL_LANG']['XPL']['customsql'][3][0] = 'Insert tags';
$GLOBALS['TL_LANG']['XPL']['customsql'][3][1] =
        'Insert tags are supported, but keep in mind that not all tags might be available
        when the filter setting is used (for example the <em>{{page::id}}</em> is
        available only when used from a front end page and not from RSS-feeds).';
$GLOBALS['TL_LANG']['XPL']['customsql'][4][0] = 'Secure insert tags';
$GLOBALS['TL_LANG']['XPL']['customsql'][4][1] =
        'Secure insert tags are just like the plain insert tags, but their value get\'s escaped in the query.<br />
        Therefore you might be better off using the secure equivalent unless you exactly know what you are doing.<br />
        The notation is like:
        <pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql'][5][0] = 'Parameter sources<br />';
$GLOBALS['TL_LANG']['XPL']['customsql'][5][1] = 'Parameter sources have the normal layout of:
        <pre>{{param::[source]?[query string]}}</pre>
        Where the source may be any of:
        <ul>
        <li><strong>get</strong> - HTTP GET query string</li>
        <li><strong>post</strong> - HTTP POST fields</li>
        <li><strong>cookie</strong> - HTTP COOKIE values</li>
        <li><strong>session</strong> - any field in the Contao session</li>
        <li><strong>filter</strong> - any of the passed filter parameters (for sharing paramters between filter settings).</li>
        <li><strong>container</strong> - Name of a callable service in the MetaModels service container (this requires additional PHP coding from your side).</li>
        </ul>
        The Query string is built like a normal HTTP query string as "name=value" pairs which are combined using the & char and must at least contain the field "name".
        One or more of the following optional keys may be used in addition:
        <ul>
        <li><strong>default</strong> - the default value to use, if there is no value available.</li>
        <li><strong>aggregate</strong> - either "list" or "set"</li>
        <li><strong>key</strong> - set to 1 to read the key of arrays (needs aggregate "set").</li>
        <li><strong>recursive</strong> - set to 1 to read arrays recursive (needs aggregate "set").</li>
        <li><strong>service</strong>  - The name of the service to retrieve (needs source "service").</li>
        </ul>
        ';
$GLOBALS['TL_LANG']['XPL']['customsql'][6][0] = 'Example 3<br />use complex filter parameter sources';
$GLOBALS['TL_LANG']['XPL']['customsql'][6][1] = '<pre>SELECT t.id
    FROM {{table}} AS t
    WHERE t.catname={{param::get?name=category&default=defaultcat}}</pre>
        <p>
        This is the same as example 2 but now we use a parameter from the "query" string.
        </p>
        <p>
        Imagine the page URL as of: "http://example.org/list/category/demo.html"<br />
        the resulting Query will then be: "SELECT t.id FROM mm_demo AS t WHERE t.catname=\'demo\'"
        </p>
        <p>
        If the URL should be: "http://example.org/list.html",<br />
        the resulting Query will then be: "SELECT t.id FROM mm_demo AS t WHERE t.catname=\'defaultcat\'"
        </p>
        ';

/* dca setting conditions */
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][0][0] = 'Attribute value is...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][0][1] =
    'The condition is fulfilled when the attribute value is equal to the specified value.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][1][0] = 'Attribute values contain any of...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][1][1] =
    'The condition is fulfilled when any of the attribute\'s values matches at least one of the specified values ' .
    '(set intersection).';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][2][0] = 'Is attribute visible...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][2][1] =
    'The condition is fulfilled when the condition of the specifiend attribute is fulfilled. In other words, the ' .
    'attribute is visible if, and only if, the specified attribute is visible as well.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][3][0] = 'OR';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][3][1] = 'Any sub condition must be fulfilled.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][4][0] = 'AND';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][4][1] = 'All sub condition must be fulfilled.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][5][0] = 'NOT';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition'][5][1] = 'Invert the result of the contained condition.';

/* dca dca (panellayout) */
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][0][0] = 'Set panel options';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][0][1] =
    'Add one or more panel options and separate with comma (= space) or semicolon (= new line) like "filter;search;sort,limit".';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][1][0] = 'Panel options for copying';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][1][1] = 'filter;search;sort,limit';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][2][0] = 'filter';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][2][1] = 'Show the filter records menu.';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][3][0] = 'search';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][3][1] = 'Show the search records menu.';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][4][0] = 'sort';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][4][1] = 'Show the sort records menu.';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][5][0] = 'limit';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout'][5][1] = 'Show the limit records menu.';

/* dca dcasetting*/
$GLOBALS['TL_LANG']['XPL']['tl_class'][0][0] = 'Set CSS classes for widget';
$GLOBALS['TL_LANG']['XPL']['tl_class'][0][1] =
    'Set one or more of the following CSS classes to define the layout of the widget like "clr w50".';
$GLOBALS['TL_LANG']['XPL']['tl_class'][1][0] = 'CSS classes for copying';
$GLOBALS['TL_LANG']['XPL']['tl_class'][1][1] = 'clr clx w50 w50x m12 wizard long';
$GLOBALS['TL_LANG']['XPL']['tl_class'][2][0] = 'clr';
$GLOBALS['TL_LANG']['XPL']['tl_class'][2][1] = 'Clear all floats.';
$GLOBALS['TL_LANG']['XPL']['tl_class'][3][0] = 'clx';
$GLOBALS['TL_LANG']['XPL']['tl_class'][3][1] =
    'Remove only the annoying overflow hidden, please use it together with "clr".';
$GLOBALS['TL_LANG']['XPL']['tl_class'][4][0] = 'w50';
$GLOBALS['TL_LANG']['XPL']['tl_class'][4][1] = 'Set the field width to 50% and float it (float:left).';
$GLOBALS['TL_LANG']['XPL']['tl_class'][5][0] = 'w50x';
$GLOBALS['TL_LANG']['XPL']['tl_class'][5][1] =
    'Remove only the annoying fixed height, please use it together with "w50".';
$GLOBALS['TL_LANG']['XPL']['tl_class'][6][0] = 'm12';
$GLOBALS['TL_LANG']['XPL']['tl_class'][6][1] = 'Add a 12 pixel top margin to the element (used for single checkboxes).';
$GLOBALS['TL_LANG']['XPL']['tl_class'][7][0] = 'wizard';
$GLOBALS['TL_LANG']['XPL']['tl_class'][7][1] =
    'Shorten the input field so there is enough room for the wizard button (e.g. date picker fields).';
$GLOBALS['TL_LANG']['XPL']['tl_class'][8][0] = 'long';
$GLOBALS['TL_LANG']['XPL']['tl_class'][8][1] = 'Set the field width to 100%.';
