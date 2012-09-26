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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel']['name']                 = array('Name', 'Name des MetaModels.');
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp']               = array('Aktualisierungsdatum', 'Datum und Zeit der letzen Aktualisierung.');
$GLOBALS['TL_LANG']['tl_metamodel']['tableName']            = array('Tabellenname', 'Name der Datenbanktabelle, in der die Datensätze gespeichert werden sollen.');

$GLOBALS['TL_LANG']['tl_metamodel']['ptable']               = array('Elterntabelle (wenn gewünscht)', 'Name der Datenbank-Tabelle, die als Elterntabelle benutzt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['mode']                 = array('List-Modus', 'List-Modus für Elterntabelle, falls man eine Parent/Child-Beziehung nutzt.');

$GLOBALS['TL_LANG']['tl_metamodel']['translated']           = array('Übersetzung', 'Auswählen falls diese MetaModell Übersetzungen und/oder Mehrsprachigkeit unterstützen soll.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages']            = array('Unterstützte Sprachen', 'Geben Sie alle Sprachen an, die für mehrsprachige Funktion genutzt werden sollen.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode']   = array('Sprache', '');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'] = array('Als Fallback benutzen', '');

$GLOBALS['TL_LANG']['tl_metamodel']['varsupport']           = array('Varianten aktivieren', 'Auswählen, falls das MetaModel Varianten unterstützen soll.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']         = 'Name, Tabelle und Weiterleitungsseite';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']      = 'Experteneinstellungen';
$GLOBALS['TL_LANG']['tl_metamodel']['display_legend']       = 'Anzeigeformat';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel']['new']                  = array('Neues MetaModel', 'Erstellen Sie ein neues MetaModel.');

$GLOBALS['TL_LANG']['tl_metamodel']['edit']                 = array('Datensätze verwalten', 'Verwalten Sie die Datensätze des MetaModels ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['copy']                 = array('MetaModel-Definition kopieren', 'Kopieren Sie die Defnitionen des MetaModels ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['delete']               = array('MetaModel löschen', 'Löschen Sie das MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['show']                 = array('MetaModel-Details', 'Sehen Sie die Details des MetaModels ID %s an.');
$GLOBALS['TL_LANG']['tl_metamodel']['editheader']           = array('MetaModel bearbeiten', 'Bearbeiten Sie das MetaModel');
$GLOBALS['TL_LANG']['tl_metamodel']['fields']               = array('Attribute definieren', 'Define attributes for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['filter']               = array('Filter definieren', 'Define filters for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings']       = array('Ausgabevorgaben definieren', 'Define render settings for MetaModel ID %s');

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] = '%s %s';
$GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'] = 'Datensatz';
$GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'] = 'Datensätze';


?>