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
 * @author     Carolina M Koehn <ck@kikmedia.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel']['name']                 = array('Name', 'Name des MetaModels.');
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp']               = array('Aktualisierungsdatum', 'Datum und Zeit der letzten Aktualisierung.');
$GLOBALS['TL_LANG']['tl_metamodel']['tableName']            = array('Tabellenname', 'Name der Datenbanktabelle, in der die Datensätze gespeichert werden sollen.');
$GLOBALS['TL_LANG']['tl_metamodel']['mode']                 = array('List-Modus', 'List-Modus für Elterntabelle, falls man eine Parent-Child-Beziehung nutzt.');
$GLOBALS['TL_LANG']['tl_metamodel']['translated']           = array('Übersetzung', 'Auswählen, falls dieses MetaModel Übersetzungen und/oder Mehrsprachigkeit unterstützen soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages']            = array('Unterstützte Sprachen', 'Geben Sie alle Sprachen an, die für die Funktion der Mehrsprachigkeit genutzt werden sollen.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode']   = array('Sprache', 'Die Sprache auswählen, die verwendet werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'] = array('Fallback', 'Die Sprache auswählen, die als Fallback-Sprache benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['varsupport']           = array('Varianten aktivieren', 'Auswählen, falls das MetaModel Varianten unterstützen soll.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']         = 'Name und Tabelle';
$GLOBALS['TL_LANG']['tl_metamodel']['translated_legend']     = 'Übersetzung';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']      = 'Experteneinstellungen';

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
$GLOBALS['TL_LANG']['tl_metamodel']['cut']                  = array('MetaModel verschieben', 'Passen Sie die Reihenfolge der MetaModels an.');

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] = '%s %s';
$GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'] = 'Datensatz';
$GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'] = 'Datensätze';