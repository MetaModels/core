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
 * Translations are managed automatically using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * Last-updated: 2024-07-10T13:37:22+00:00
 *
 * @copyright 2012-2024 The MetaModels team.
 * @license   https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @link      https://www.transifex.com/metamodels/public/
 * @link      https://www.transifex.com/signup/?join_project=metamodels
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Zusammenfassung';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1']            = 'Geben Sie eine beliebige SQL-Abfrage ein, die ausgeführt werden soll.<br />
        Es ist erforderlich, dass diese Abfrage mindestens eine Spalte mit dem Namen "id" zurückgibt.<br />
       Es ist nicht möglich, Berechnungen aus dem SQL an die Liste zu übergeben.<br />
        Die Deklaration der Spaltennamen sollte mit dem Tabellenalias als Präfix eingegeben werden, z.B. t.name.';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Beispiel 1<br />Einfache Abfrage';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1']            = '<pre>SELECT t.id FROM mm_mymetamodel AS t WHERE t.page_id=1</pre>
        Damit werden alle IDs aus der Tabelle <em>mm_mymetamodel</em> ausgewählt, mit <em>page_id=1</em>
        ';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Beispiel 2<br />Tabellennamen einsetzen';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1']            = '<pre>SELECT t.id FROM {{table}} AS t WHERE t.page_id=1</pre>
        Dies ist lediglich dasselbe wie in Beispiel 1, aber der Tabellenname des aktuellen MetaModel
        (z.B.: das <em>mm_mymetamodel</em> von oben) wird in die Abfrage eingefügt.';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0']            = 'Inserttags';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1']            = 'Insert-Tags werden unterstützt. Bitte beachten, dass nicht alle Tags für alle Ausgaben verfügbar sein können. Falls eine Filtereinstellung wie zum Beispiel <em>{{page::id}}</em> benutzt wird, dann ist der Insert-Tag nur für einen Seitenaufruf im Frontend und nicht für einen RRS-Feed verfügbar.';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0']            = 'Sichere Inserttags';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1']            = 'Sichere Insert-Tags funktionieren wie normale Insert-Tags. Allerdings werden die Werte in der Abfrage escaped.<br /> Eine unbedachte Nutzung kann daher zu unerwarteten Ergebnissen führen.<br /> Die Notation für sichere Insert-Tags ist wie folgt:<br /> <pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0']            = 'Parameterquellen';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1']            = 'Parameterquellen sind nach diesem Muster aufgebaut: <pre>{{param::[source]?[query string]}}</pre> Eine Quelle kann bestehen aus <ul> <li><strong>get</strong> - HTTP GET Query-String</li> <li><strong>post</strong> - HTTP POST Feldern</li> <li><strong>session</strong> - einem beliebigen Feld aus der Contao-Session</li> <li><strong>filter</strong> - einen beliebigen ausgeführten Filterparameter (um Filterparameter zwischen Filtereinstellungen zu teilen).</li> </ul> Der Abfragestring wird wie ein normaler HTTP-Query-String als "name=wert"-Paar aufgebaut kann mit dem Zeichen & kombiniert werden und muss mindestens das Feld \'name\' enthalten. Einer oder mehrere der folgenden optionalen Schlüsselwörter können zusätzlich benutzt werden; <ul> <li><strong>default</strong> - der zu benutzende Standardwert falls kein anderer zur Verfügung steht</li> <li><strong>aggregate</strong> - entweder "list" oder "set"</li> <li><strong>key</strong> - auf 1 setzen um den Schlüssel eines Array auszulesen (benötigt "aggregate").</li> <li><strong>recursive</strong> - auf 1 setzen um Arrays rekursiv auszulesen (benötigt "aggregate").</li> </ul>';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0']            = 'Beispiel 3<br />
Komplexe Filter, Parameter und Quellen nutzen';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1']            = '<pre>SELECT t.id
    FROM {{table}} AS t
    WHERE t.catname={{param::get?name=category&default=defaultcat}}</pre>
        <p>
        Dies ist dasselbe wie in Beispiel 2, aber jetzt verwenden wir einen Parameter aus dem "query"-String.
        </p>
        <p>
        bei der Beispiel-URL wie diese: "http://example.org/list/category/demo.html"<br />
        wäre das Query: "SELECT t.id FROM mm_demo AS t WHERE t.catname=\'demo\'"
        </p>
        <p>
        Ist die URL: "http://example.org/list.html",<br />
       wäre das Query: "SELECT t.id FROM mm_demo AS t WHERE t.catname=\'defaultcat\'"
        </p>
        ';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['0']['0']      = 'Panel-Optionen';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['0']['1']      = 'Fügen Sie eine oder mehrere Panel-Optionen hinzu und trennen Sie diese mit einem Komma (= Freiraum) oder Semikolon (= neue Zeile), z. B. "filter;search;sort,limit".';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['1']['0']      = 'Panel-Optionen zum Kopieren';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['1']['1']      = 'filter;search;sort,limit';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['2']['0']      = 'Filtern';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['2']['1']      = 'Zeige Filter in der Listendarstellung';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['3']['0']      = 'Suche';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['3']['1']      = 'Zeige Suche in der Listendarstellung';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['4']['0']      = 'Sortierung';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['4']['1']      = 'Zeige Sortierung in der Listendarstellung';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['5']['0']      = 'Limit';
$GLOBALS['TL_LANG']['XPL']['dca_panellayout']['5']['1']      = 'Zeige Limit in der Listendarstellung';
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
$GLOBALS['TL_LANG']['XPL']['tl_class']['0']['0']             = 'Die CSS-Klasse für das Eingabewidget setzen';
$GLOBALS['TL_LANG']['XPL']['tl_class']['0']['1']             = 'Legen Sie eine oder mehrere der folgenden CSS-Klassen fest, um das Layout des Widgets wie "clr w50" zu definieren.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['1']['0']             = 'CSS-Klassen zum Kopieren';
$GLOBALS['TL_LANG']['XPL']['tl_class']['1']['1']             = 'clr clx w50 w50x m12 wizard long';
$GLOBALS['TL_LANG']['XPL']['tl_class']['2']['0']             = 'clr';
$GLOBALS['TL_LANG']['XPL']['tl_class']['2']['1']             = 'Hebt alle Floats auf.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['3']['0']             = 'clx';
$GLOBALS['TL_LANG']['XPL']['tl_class']['3']['1']             = 'Entfernt die Voreinstellung "overflow:hidden". Bitte gemeinsam mit "clr" verwenden.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['4']['0']             = 'w50';
$GLOBALS['TL_LANG']['XPL']['tl_class']['4']['1']             = 'Setzt die Feldbreite auf 50% und floated links (float:left).';
$GLOBALS['TL_LANG']['XPL']['tl_class']['5']['0']             = 'w50x';
$GLOBALS['TL_LANG']['XPL']['tl_class']['5']['1']             = 'Entfernt die Voreinstellung für eine feste Höhe. Bitte gemeinsam mit "w50" verwenden.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['6']['0']             = 'm12';
$GLOBALS['TL_LANG']['XPL']['tl_class']['6']['1']             = 'Fügt dem Element einen oberen Abstand von 12 Pixeln hinzu (z.B. für einzelne Checkboxen).';
$GLOBALS['TL_LANG']['XPL']['tl_class']['7']['0']             = 'wizard';
$GLOBALS['TL_LANG']['XPL']['tl_class']['7']['1']             = 'Verkürzt das Eingabefeld, damit genug Platz für den Wizard (z.B. einen Date Picker) ist.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['8']['0']             = 'long';
$GLOBALS['TL_LANG']['XPL']['tl_class']['8']['1']             = 'Setzt das Feld auf 100% Breite.';
