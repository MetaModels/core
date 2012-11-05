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
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['name']                 = array('Name', 'Einstellungsname.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['tstamp']               = array('Änderungsdatum', 'Datum und Zeit der letzten Änderung.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['isdefault']            = array('Standard', 'Bestimmt, ob diese Einstellung als Standardvorgabe bei Eltern-Kind-Beziehungen im MetaModel benutzt werden soll, wenn in einem Modul/Contentelement/etc. keines explizit ausgewählt wurde.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['template']             = array('Template', 'Das Template festlegen, das für die Darstellung der Datensätze benutzt werden soll.');

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo']               = array('Zielseite', 'Die Seite (URL) festlegen, die für die Darstellung der Details benutzt wird. Detaillierte URL-Parameter werden von der jeweilig benutzten Filtereinstellung generiert.');
 

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings_legend']      = 'Einstellungen';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['new']                  = array('Neu', 'Neue Einstellung erstellen.');

$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['edit']                 = array('Einstellung bearbeiten', 'Die Einstellung ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['copy']                 = array('Einstellungsdefinition kopieren', 'Die Einstellungsdefinition ID %s kopieren');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['delete']               = array('Einstellungsdefinition löschen', 'Die Einstellungsdefinition ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['show']                 = array('Filterdetails', 'Die Details der Einstellungsdefinition ID %s anzeigen.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings']               = array('Attributeinstellungen definieren', 'Die Attributeinstllungen für ID %s definieren.');

?>