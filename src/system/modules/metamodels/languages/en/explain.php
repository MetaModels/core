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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}


/**
 * Insert tags
 */
$GLOBALS['TL_LANG']['XPL']['customsql'] = array
(
	array
	(
		'Abstract',
		'Type in any SQL query that shall be executed.<br />
		It is expected, that this query returns at least one column named "id".
		'
	),
	array
	(
		'Insert tags',
		'Insert tags are supported, but keep in mind that not all tags might be available
		when the filter setting is used (for example the <em>{{page::id}}</em> is
		available only when used from a frontend page and not from RSS-feeds).'
	),
	array
	(
		'Example 1<br />plain query',
		'<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
		This selects all ids from the table <em>mm_mymetamodel</em> that have the value page_id=1
		'
	),
	array
	(
		'Example 2<br />insert tablename',
		'<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
		This is merely the same as example 1 but the table name of the current metamodel (i.e.: the <em>mm_mymetamodel</em> from above) will get inserted into the query.
		'
	)
);

?>