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
 * @author Carolina M Koehn <ck@kikmedia.de>
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel']['name']                 = array('Name', 'Name des MetaModels.');
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp']               = array('Aktualisierungsdatum', 'Datum und Zeit der letzten Aktualisierung.');
$GLOBALS['TL_LANG']['tl_metamodel']['tableName']            = array('Tabellenname', 'Name der Datenbanktabelle, in der die Datensätze gespeichert werden sollen.');

$GLOBALS['TL_LANG']['tl_metamodel']['ptable']               = array('Elterntabelle, falls gewünscht)', 'Name der Datenbanktabelle, die als Elterntabelle benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['mode']                 = array('List-Modus', 'List-Modus für Elterntabelle, falls man eine Parent-Child-Beziehung nutzt.');

$GLOBALS['TL_LANG']['tl_metamodel']['translated']           = array('Übersetzung', 'Auswählen, falls dieses MetaModel Übersetzungen und/oder Mehrsprachigkeit unterstützen soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages']            = array('Unterstützte Sprachen', 'Geben Sie alle Sprachen an, die für die Funktion der Mehrsprachigkeit genutzt werden sollen.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode']   = array('Sprache', 'Die Sprache auswählen, die verwendet werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'] = array('Fallback', 'Die Sprache auswählen, die als Fallback-Sprache benutzt werden soll.');

$GLOBALS['TL_LANG']['tl_metamodel']['varsupport']           = array('Varianten aktivieren', 'Auswählen, falls das MetaModel Varianten unterstützen soll.');

$GLOBALS['TL_LANG']['tl_metamodel']['rendertype']           = array('Integration', 'Die gewünschte Art der Integration auswählen.');

$GLOBALS['TL_LANG']['tl_metamodel']['backendsection']       = array('Backend-Bereich', 'Den gewünschten Backend-Bereich auswählen, in dem das MetaModel erscheinen soll. Für Inhalte, die von Redakteuren bearbeitet werden sollen, wird der Bereich "Inhalte" empfohlen.');

$GLOBALS['TL_LANG']['tl_metamodel']['backendcaption']       = array('Backend-Beschreibung', 'Der hier angegebene Text wird als Bereichsüberschrift und Beschreibung in der Backend-Navigation erscheinen.');

$GLOBALS['TL_LANG']['tl_metamodel']['becap_langcode']       = array('Sprachen', 'Die Sprachen auswählen, die angeboten werden sollen.');
$GLOBALS['TL_LANG']['tl_metamodel']['becap_label']          = array('Menütext', 'Der hier angegebene Text wird als Menütext im Backend benutzt.');
$GLOBALS['TL_LANG']['tl_metamodel']['becap_description']    = array('Beschreibung', 'Der hier angegebene Text wird für die beim Hovern sichtbare erweiterte Beschreibung im Backend benutzt.');

$GLOBALS['TL_LANG']['tl_metamodel']['backendicon']          = array('Backend-Icon', 'Ein Icon für die Darstellung im Backend auswählen. Dieses Icon wird links vom Menütext angezeigt und ermöglicht eine schnelle Orientierung. In der Baumansicht wird das Icon ebenfalls im oberen Bereich benutzt.');
/**
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']         = 'Name, Tabelle und Weiterleitungsseite';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']      = 'Experteneinstellungen';
$GLOBALS['TL_LANG']['tl_metamodel']['backend_legend']       = 'Backend-Integration';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel']['new']                  = array('Neues MetaModel', 'Erstellen Sie ein neues MetaModel.');

$GLOBALS['TL_LANG']['tl_metamodel']['edit']                 = array('Datensätze verwalten', 'Verwalten Sie die Datensätze des MetaModels ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['copy']                 = array('MetaModel-Definition kopieren', 'Kopieren Sie die Definitionen des MetaModels ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['delete']               = array('MetaModel löschen', 'Löschen Sie das MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['show']                 = array('MetaModel-Details', 'Sehen Sie die Details des MetaModels ID %s an.');
$GLOBALS['TL_LANG']['tl_metamodel']['editheader']           = array('MetaModel bearbeiten', 'Bearbeiten Sie das MetaModel');
$GLOBALS['TL_LANG']['tl_metamodel']['fields']               = array('Attribute definieren', 'Definieren Sie Attribute für das MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['filter']               = array('Filter definieren', 'Definieren Sie Filter für das MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings']       = array('Ausgabevorgaben definieren', 'Definieren Sie Ausgabevorgaben für das MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['dca']                  = array('Paletteneinstellungen', 'Paletteneinstellungen für das MetaModel ID %s definieren.');
$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine']          = array('Paletten- und Ansichtseinstellungen festlegen', 'Paletten- und Ansichtseinstellungen für MetaModel ID %s festlegen');


/**
 * Reference
 */

$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_0'] = '0 Datensätze unsortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_1'] = '1 Datensätze nach bestimmtem Feld sortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_2'] = '2 Datensätze nach wählbarem Feld sortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_3'] = '3 Datensätze nach Elterntabelle sortiert darstellen';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_4'] = '4 Kinddatensätze einer Elterntabelle anzeigen (wie im CSS-Modul)';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_5'] = '5 Baumdarstellung (wie Seitenstruktur)';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_6'] = '6 Kinddatensätze in einer Baumstruktur darstellen (wie Artikelmodul)';

$GLOBALS['TL_LANG']['tl_metamodel']['rendertypes']['standalone'] = 'Als Einzeltabelle';
$GLOBALS['TL_LANG']['tl_metamodel']['rendertypes']['ctable']     = 'Als Kindtabelle';


/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] = '%s %s';
$GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'] = 'Datensatz';
$GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'] = 'Datensätze';


?>