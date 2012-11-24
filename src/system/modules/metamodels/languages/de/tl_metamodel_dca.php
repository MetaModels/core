<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['name']                 = array('Name', 'Name der Palette.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp']               = array('Aktualisierungsdatum', 'Datum und Zeit der letzten Aktualisierung.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdefault']            = array('Als Standard benutzen', 'Bestimmt, ob diese Palette als Standardvorgabe für Elterndatensätze benutzt werden soll.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype']           = array('Integration', 'Die gewünschte Art der Integration auswählen.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable']               = array('Elterntabelle (falls gewünscht)', 'Name der Datenbanktabelle, die als Elterntabelle benutzt werden soll.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['mode']                 = array('Sortiermodus', 'Sortiermodus, der für die Darstellung der Datensätze benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['flag']                 = array('Sortierung', 'Sortierung in der Datensatzansicht.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection']       = array('Backend-Bereich', 'Den gewünschten Backend-Bereich auswählen, in dem das MetaModel erscheinen soll. Für Inhalte, die von Redakteuren bearbeitet werden sollen, wird der Bereich "Inhalte" empfohlen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon']          = array('Backend-Icon', 'Ein Icon für die Darstellung im Backend auswählen. Dieses Icon wird links vom Menütext angezeigt und ermöglicht eine schnelle Orientierung. In der Baumansicht wird das Icon ebenfalls im oberen Bereich benutzt.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption']       = array('Backend-Beschreibung', 'Der hier angegebene Text wird als Bereichsüberschrift und Beschreibung in der Backend-Navigation erscheinen.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode']       = array('Sprachen', 'Die Sprachen auswählen, die angeboten werden sollen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label']          = array('Menütext', 'Der hier angegebene Text wird als Menütext im Backend benutzt.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description']    = array('Beschreibung', 'Der hier angegebene Text wird für die beim Hovern sichtbare erweiterte Beschreibung im Backend benutzt.');


$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout']          = array('Panel-Layout', 'Die Darstellungsoptionen des Panels mit Komma (= Zwischenraum) und Semikolon (= neue Zeile) bestimmen - wie bei sort,filter;search,limit.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelpicker']          = 'Panelpicker';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['use_limitview']        = array('Anzeigeeinschränkungen', 'Aktiviert die Anzeigeeinschränkungen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['limit_rendersetting']  = array('Rendern beschränken', 'Zwischen Frontend und Backend wählen.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['view_legend']          = 'Ansichtseinstellungen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backend_legend']       = 'Backend-Integration';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new']                  = array('Neue Palette', 'Eine neue Palette erstellen');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit']                 = array('Palette bearbeiten', 'Die Palette ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy']                 = array('Palettendefinition kopieren', 'Die Palettendefinition von ID %s kopieren');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete']               = array('Palette löschen', 'Die Palette ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show']                 = array('Palettendetails', 'Die Details der Palette ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings']             = array('Paletteneinstellungen', 'Die Einstellungen der Palette ID %s bearbeiten');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['1']     = 'Nach dem ersten Buchstaben absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['2']     = 'Nach dem ersten Buchstaben aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['3']     = 'Nach den ersten beiden Buchstaben absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['4']     = 'Nach den ersten beiden Buchstaben aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['5']     = 'Nach Tag aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['6']     = 'Nach Tag absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['7']     = 'Nach Monat aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['8']     = 'Nach Monat absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['9']     = 'Nach Jahr aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['10']    = 'Nach Jahr absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['11']    = 'Aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['12']    = 'Absteigend sortieren';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_0'] = '0 Datensätze unsortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_1'] = '1 Datensätze nach bestimmtem Feld sortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_2'] = '2 Datensätze nach wählbarem Feld sortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_3'] = '3 Datensätze nach Elterntabelle sortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_4'] = '4 Kinddatensätze einer Elterntabelle anzeigen (wie im CSS-Modul)';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_5'] = '5 Baumdarstellung (wie Seitenstruktur)';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_6'] = '6 Kinddatensätze in einer Baumstruktur darstellen (wie Artikelmodul)';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['standalone'] = 'Als Einzeltabelle';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['ctable']     = 'Als Kindtabelle';



?>