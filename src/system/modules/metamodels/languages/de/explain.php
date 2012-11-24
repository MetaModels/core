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
 * @author Carolina M Koehn <ck@kikmedia.de>
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
		Dabei wird erwartet, dass mindestens eine Spalte mit der Bezeichnung "id" zurückgegeben wird.
		'
	),
	array
	(
		'Inserttags',
		'Inserttags werden unterstützt. Bitte beachten Sie, dass nicht immer alle Inserttags zur Verfügung stehen müssen, falls eine Filtereinstellung benutzt wird. Beispielsweise ist <em>{{env::page}}</em> nur für Frontenddarstellung möglich, nicht aber für RSS-Feeds.'
	),
	array
	(
		'Beispiel 1<br />normale SQL-Abfrage',
		'<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
		Damit werden alle IDs der Tabelle <em>mm_mymetamodel</em> ausgewählt, die den Wert <em>page_id=1</em> besitzen.'
	),
	array
	(
		'Beispiel 2<br />dynamisches Einfügen eines Tabellennamens',
		'<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
		Beinahe identisch zu Beispiel 1, außer dass der Tabellenname des aktuellen MetaModels (also <em>mm_mymetamodel</em> wie oben) in die Abfrage eingefügt wird.
		'
	)
);

?>