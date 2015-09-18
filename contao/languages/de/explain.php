<?php
/**
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/p/metamodels/language/de/
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * last-updated: 2015-08-04T04:10:59+02:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0'] = 'Zusammenfassung';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1'] = 'Geben Sie eine SQL-Abfrage ein die ausgeführt werden soll.<br />Diese Abfrage muss zwingend mindestens eine Spalte mit dem Namen "id" zurück liefern.';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0'] = 'Beispiel 1<br />Einfache Abfrage';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1'] = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre> Dieses selektiert alle IDs von der Tabelle <em>mm_mymetamodel</em> bei welchen Wert <em>page_id=1</em> ist.
';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0'] = 'Beispiel 2<br />Tabellennamen einsetzen';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1'] = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre> Nahezu gleich wie in Beispiel 1, außer dass der Tabellenname des aktuellen MetaModels (also: das <em>mm_mymetamodel</em> von oben) in die Abfrage eingefügt wird. ';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0'] = 'Inserttags';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1'] = 'Insert-Tags werden unterstützt. Bitte beachten, dass nicht alle Tags für alle Ausgaben verfügbar sein können. Falls eine Filtereinstellung wie zum Beispiel <em>{{page::id}}</em> benutzt wird, dann ist der Insert-Tag nur für einen Seitenaufruf im Frontend und nicht für einen RRS-Feed verfügbar.';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0'] = 'Sichere Inserttags';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1'] = 'Sichere Insert-Tags funktionieren wie normale Insert-Tags. Allerdings werden die Werte in der Abfrage escaped.<br /> Eine unbedachte Nutzung kann daher zu unerwarteten Ergebnissen führen.<br /> Die Notation für sichere Insert-Tags ist wie folgt:<br /> <pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0'] = 'Parameterquellen';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1'] = 'Parameterquellen sind nach diesem Muster aufgebaut: <pre>{{param::[source]?[query string]}}</pre> Eine Quelle kann bestehen aus <ul> <li><strong>get</strong> - HTTP GET Query-String</li> <li><strong>post</strong> - HTTP POST Feldern</li> <li><strong>session</strong> - einem beliebigen Feld aus der Contao-Session</li> <li><strong>filter</strong> - einen beliebigen ausgeführten Filterparameter (um Filterparameter zwischen Filtereinstellungen zu teilen).</li> </ul> Der Abfragestring wird wie ein normaler HTTP-Query-String als "name=wert"-Paar aufgebaut kann mit dem Zeichen & kombibiert werden und muss mindestens das Feld \'name\' enthalten. Einer oder mehrere der folgenden optionalen Schlüsselwörter können zusätzlich benutzt werden; <ul> <li><strong>default</strong> - der zu benutzende Standardwert falls kein anderer zur Verfügung steht</li> <li><strong>aggregate</strong> - entweder "list" oder "set"</li> <li><strong>key</strong> - auf 1 setzen um den Schlüssel eines Array auszulesen (benötigt "aggregate").</li> <li><strong>recursive</strong> - auf 1 setzen um Arrays rekursiv auszulesen (benötigt "aggregate").</li> </ul>';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0'] = 'Beispiel 3<br />
Komplexe Filter, Parameter und Quellen nutzen';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1'] = 'pre>SELECT id FROM {{table}} WHERE catname={{param::get?name=category&default=defaultcat}}</pre> <p> Dies ist prinzipiell ähnlich wie in Beispiel 2. Allerdings wird hier ein Parameter aus dem "query"-String verwendet. </p> <p> Stellen Sie sich eine Seiten-URL wie "http://example.org/list/category/demo.html" vor.<br /> Die Abfrage lautet dann: "SELECT id FROM mm_demo WHERE catname=\'demo\'" </p> <p> Falls die URL "http://example.org/list.html" lauten soll müsste die Abfrage dann "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'" lauten. </p> ';

