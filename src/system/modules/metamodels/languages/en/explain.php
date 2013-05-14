<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Insert tags
 */
$GLOBALS['TL_LANG']['XPL']['customsql'] = array
(
	array
	(
		'Abstract',
		'Type in any SQL query that shall be executed.<br />
		It is required that this query returns at least one column named "id".
		'
	),
	array
	(
		'Example 1<br />plain query',
		'<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
		This selects all IDs from the table <em>mm_mymetamodel</em> that have the value <em>page_id=1</em>
		'
	),
	array
	(
		'Example 2<br />insert tablename',
		'<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
		This is merely the same as example 1 but the table name of the current MetaModel (i.e.: the <em>mm_mymetamodel</em> from above) will get inserted into the query.
		'
	),
	array
	(
		'Insert tags',
		'Insert tags are supported, but keep in mind that not all tags might be available
		when the filter setting is used (for example the <em>{{page::id}}</em> is
		available only when used from a front end page and not from RSS-feeds).'
	),
	array
	(
		'Secure insert tags',
		'Secure insert tags are just like the plain insert tags, but their value get\'s escaped in the query.<br />
		Therefore you might be better off using the secure equivalent unless you exactly know what you are doing.<br />
		The notation is like:
		<pre>{{secure::page::id}}</pre>'
	),
	array
	(
		'Parameter sources<br />',
		'Parameter sources have the normal layout of:
		<pre>{{param::[source]?[query string]}}</pre>
		Where the source may be any of:
		<ul>
		<li><strong>get</strong> - HTTP GET query string</li>
		<li><strong>post</strong> - HTTP POST fields</li>
		<li><strong>session</strong> - any field in the Contao session</li>
		<li><strong>filter</strong> - any of the passed filter parameters (for sharing paramters between filter settings).</li>
		</ul>
		The Query string is built like a normal HTTP query string as "name=value" pairs which are combined using the & char and must at least contain the field "name".
		One or more of the following optional keys may be used in addition:
		<ul>
		<li><strong>default</strong> - the default value to use, if there is no value available.</li>
		<li><strong>aggregate</strong> - either "list" or "set"</li>
		<li><strong>key</strong> - set to 1 to read the key of arrays (needs aggregate set).</li>
		<li><strong>recursive</strong> - set to 1 to read arrays recursive (needs aggregate set).</li>
		</ul>
		'
	),
	array
	(
		'Example 3<br />use complex filter parameter sources',
		'<pre>SELECT id
	FROM {{table}}
	WHERE catname={{param::get?name=category&default=defaultcat}}</pre>
		<p>
		This is the same as example 2 but now we use a parameter from the "query" string.
		</p>
		<p>
		Imagine the page URL as of: "http://example.org/list/category/demo.html"<br />
		the resulting Query will then be: "SELECT id FROM mm_demo WHERE catname=\'demo\'"
		</p>
		<p>
		If the URL should be: "http://example.org/list.html",<br />
		the resulting Query will then be: "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'"
		</p>
		'
	)
);

