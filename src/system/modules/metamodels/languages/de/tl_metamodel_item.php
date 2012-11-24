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
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_item']['new']           = array('Neuer Datensatz', 'Einen neuen Datensatz erstellen.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['edit']          = array('Datensatz bearbeiten', 'Den Datensatz ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['copy']          = array('Datensatz kopieren', 'Den Datensatz ID %s kopieren.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['createvariant'] = array('Neue Variante', 'Eine neue Variante von datensatz ID %s erstellen');
$GLOBALS['TL_LANG']['tl_metamodel_item']['cut']           = array('Datensatz verschieben', 'Den Datensatz ID %s verschieben.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['delete']        = array('Datensatz löschen', 'Den Datensatz ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_item']['show']          = array('Datensatz-Details', 'Die Details von Datensatz ID %s anzeigen.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['editheader']    = array('Datensatztyp bearbeiten', 'Den typ des Datensatzes bearbeiten.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['fields']        = array('Attribute verwalten', 'Die Attribute dieses MetaModels verwalten.');

$GLOBALS['TL_LANG']['tl_metamodel_item']['pastenew']      = array('Neuer Datensatz', 'Einen neuen datensatz hinter Datensatz ID %s erzeugen');

$GLOBALS['TL_LANG']['tl_metamodel_item']['varbase']       = array('Ist Basis für Varianten', 'Aktivieren, falls dieser Datensatz als Basis für die aktuelle Variantengruppe benutzt werden soll.');

?>