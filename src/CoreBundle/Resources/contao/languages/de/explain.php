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
 * Translations are managed automatically using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * Last-updated: 2019-05-03T19:20:58+02:00
 *
 * @copyright 2012-2019 The MetaModels team.
 * @license   https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @link      https://www.transifex.com/metamodels/public/
 * @link      https://www.transifex.com/signup/?join_project=metamodels
 */


$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Zusammenfassung';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1']            = 'Geben Sie eine SQL-Abfrage ein, die ausgeführt werden soll.<br />Diese Abfrage muss zwingend mindestens eine Spalte mit dem Namen "id" zurück liefern.';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Beispiel 1<br />Einfache Abfrage';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1']            = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
Dieses selektiert alle IDs von der Tabelle <em>mm_mymetamodel</em>, bei welchen der Wert <em>page_id=1</em> ist.
';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Beispiel 2<br />Tabellennamen einsetzen';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1']            = '<pre>SELECT id
FROM {{table}}
WHERE page_id=1</pre> Nahezu gleich wie in Beispiel 1, außer dass der Tabellenname des aktuellen MetaModels (also: das <em>mm_mymetamodel</em> von oben) in die Abfrage eingefügt wird. ';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0']            = 'Inserttags';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1']            = 'Insert-Tags werden unterstützt. Bitte beachten, dass nicht alle Tags für alle Ausgaben verfügbar sein können. Falls eine Filtereinstellung wie zum Beispiel <em>{{page::id}}</em> benutzt wird, dann ist der Insert-Tag nur für einen Seitenaufruf im Frontend und nicht für einen RRS-Feed verfügbar.';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0']            = 'Sichere Inserttags';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1']            = 'Sichere Insert-Tags funktionieren wie normale Insert-Tags. Allerdings werden die Werte in der Abfrage escaped.<br /> Eine unbedachte Nutzung kann daher zu unerwarteten Ergebnissen führen.<br /> Die Notation für sichere Insert-Tags ist wie folgt:<br /> <pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0']            = 'Parameterquellen';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1']            = 'Parameterquellen sind nach diesem Muster aufgebaut: <pre>{{param::[source]?[query string]}}</pre> Eine Quelle kann bestehen aus <ul> <li><strong>get</strong> - HTTP GET Query-String</li> <li><strong>post</strong> - HTTP POST Feldern</li> <li><strong>session</strong> - einem beliebigen Feld aus der Contao-Session</li> <li><strong>filter</strong> - einen beliebigen ausgeführten Filterparameter (um Filterparameter zwischen Filtereinstellungen zu teilen).</li> </ul> Der Abfragestring wird wie ein normaler HTTP-Query-String als "name=wert"-Paar aufgebaut kann mit dem Zeichen & kombiniert werden und muss mindestens das Feld \'name\' enthalten. Einer oder mehrere der folgenden optionalen Schlüsselwörter können zusätzlich benutzt werden; <ul> <li><strong>default</strong> - der zu benutzende Standardwert falls kein anderer zur Verfügung steht</li> <li><strong>aggregate</strong> - entweder "list" oder "set"</li> <li><strong>key</strong> - auf 1 setzen um den Schlüssel eines Array auszulesen (benötigt "aggregate").</li> <li><strong>recursive</strong> - auf 1 setzen um Arrays rekursiv auszulesen (benötigt "aggregate").</li> </ul>';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0']            = 'Beispiel 3<br />
Komplexe Filter, Parameter und Quellen nutzen';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1']            = '<pre>SELECT id
FROM {{table}}
WHERE catname={{param::get?name=category&default=defaultcat}}</pre> <p> Dies ist prinzipiell ähnlich wie in Beispiel 2. Allerdings wird hier ein Parameter aus dem "query"-String verwendet. </p> <p> Stellen Sie sich eine Seiten-URL wie "http://example.org/list/category/demo.html" vor.<br /> Die Abfrage lautet dann: "SELECT id FROM mm_demo WHERE catname=\'demo\'" </p> <p> Falls die URL "http://example.org/list.html" lauten soll müsste die Abfrage dann "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'" lauten. </p> ';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['0']['0'] = 'Eigenschaftswert ist gleich ...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['0']['1'] = 'Die Bedingung ist erfüllt, wenn der Attributwert gleich dem festgelegten Wert ist. Als Attribute können diejenigen mit Einfachauswahl wie z.B. Select oder Checkbox ausgewählt werden.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['1']['0'] = 'Eigenschaftswert beinhaltet ...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['1']['1'] = 'Die Bedingung ist erfüllt, wenn ein beliebiger Attributwert gleich dem jeweils festgelegten Wert ist (Schnittmenge bzw. ODER). Als Attribute können diejenigen mit Mehrfachauswahl wie z.B. Tags ausgewählt werden.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['2']['0'] = 'Eigenschaft ist sichtbar ...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['2']['1'] = 'Die Bedingung ist erfüllt, wenn alle Bedingungen für ein ausgewähltes Attribut erfüllt sind. Mit anderen Worten, das Attribut ist sichtbar, und nur dann, wenn das ausgewählte (oder "referenzierte") Attribut auch sichtbar ist. Mit diesem Bedingungstyp erspart man sich das Duplizieren von erstellten Ansichtsbedingungen eines Attributs.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['0'] = 'ODER';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['1'] = 'Eine beliebige Bedingung muss erfüllt sein.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['4']['0'] = 'UND';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['4']['1'] = 'Alle Bedingungen müssen erfüllt sein.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['5']['0'] = 'NICHT';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['5']['1'] = 'Kehrt das Ergebnis einer vorgegebenen Bedingung um.';

