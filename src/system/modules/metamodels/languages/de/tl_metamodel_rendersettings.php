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
 * @translation Carolina M Koehn <ck@kikmedia.de>
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['general_legend']       = 'Allgemeine Einstellungen';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings_legend']      = 'Einstellungen';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['expert_legend']        = 'Experteneinstellungen';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['name']                 = array('Name', 'Einstellungsname.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['tstamp']               = array('Aktualisierungsdatum', 'Datum und Zeit der letzten Aktualisierung.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['isdefault']            = array('Standard', 'Bestimmt, ob diese Einstellung als Standardvorgabe bei Eltern-Kind-Beziehungen im MetaModel benutzt werden soll, wenn in einem Modul oder Inhaltselement keines explizit ausgewählt wurde.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['template']             = array('Template', 'Das Template festlegen, das für die Darstellung der Datensätze benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo']               = array('Zielseite', 'Die Seite (URL) festlegen, die für die Darstellung der Details benutzt wird. Detaillierte URL-Parameter werden von der jeweiligen Filtereinstellung generiert.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo']['allLanguages'] = 'Alle Sprachen';

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['mode']                 = array('Sortiermodus', 'Den Sortiermodus einstellen, der in der Datensatzdarstellung benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['flag']                 = array('Sortierung', 'Sortierungsart, die in der Datensatzansicht benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelLayout']          = array('Panel-Layout', 'Für Zwischenräume die Optionen mit einem Komma (für Leerzeichen) und Semikolon (für eine neue Zeile) eingeben. Beispiel: sort,filter;search,limit.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelpicker']          = 'Panelpicker';

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['fields']               = array('Feldeinstellungen', 'Die Header-Felder konfigurieren, um eine bessere Bedienbarkeit zu erreichen.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['field_attribute']      = 'Attributname';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['field_filterable']     = 'Filterbar';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['field_sortable']       = 'Sortierbar';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['field_searchable']     = 'Durchsuchbar';

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['use_limitview']        = array('Ansicht beschränken', 'Die Beschränkungen für die Ansicht aktivieren.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['limit_rendersetting']  = array('Rendering-Einstellungen beschränken', 'Zwischen Frontend und Backend wählen.');

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingmode']['1']     = 'Datensätze werden nach einem fest vorgegebenen Feld sortiert';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingmode']['2']     = 'Datensätze werden nach einem wechselbaren Feld sortiert';

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['1']     = 'Nach erstem Buchstaben aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['2']     = 'Nach erstem Buchstaben absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['3']     = 'Nach den ersten beiden Buchstaben aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['4']     = 'Nach den ersten beiden Buchstaben absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['5']     = 'Nach Tag aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['6']     = 'Nach Tag absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['7']     = 'Nach Monat aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['8']     = 'Nach Monat absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['9']     = 'Nach Jahr aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['10']    = 'Nach Jahr absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['11']    = 'Aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['sortingflag']['12']    = 'Absteigend sortieren';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['new']                  = array('Neu', 'Neue Einstellung erstellen');

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['edit']                 = array('Einstellung bearbeiten', 'Die Einstellung ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['copy']                 = array('Einstellungsdefinition kopieren', 'Die Einstellungsdefinition ID %s kopieren');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['delete']               = array('Einstellungsdefinition löschen', 'Die Einstellungsdefinition ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['show']                 = array('Filterdetails', 'Die Details der Einstellungsdefinition ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings']             = array('Attributeinstellungen definieren', 'Die Attributeinstellungen für ID %s definieren');

