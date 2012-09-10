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
 * @translation Carolina M Koehn <ck@kikmedia.de>
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
		'Beschreibung',
		'Geben Sie eine beliebige SQL-Abfrage ein, die ausgeführt werden soll.<br />
		Dabei wird erwartet, das mindestens eine Spalte mit der Bezeichnung "id" zurüchgegeben wird.
		'
	),
	array
	(
		'Insert tags',
		'Insert tags are supported, but keep in mind that not all tags might be available
		when the filter setting is used (for example the <em>{{env::page}}</em> is
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