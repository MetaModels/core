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
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type']                 = array('Attribut-Typ', 'Wählen Sie den Typ dieses Attributes aus. WARNUNG: Wenn Sie den Attribut-Typ ändern werden die vorhandenen Daten gelöscht.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name']                 = array('Name', 'Name als Klartext');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description']          = array('Beschreibung', 'Beschreibung als Klartext');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname']              = array('Spaltenname', 'Interner Referenzname für dieses Attribut');

$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant']            = array('Varianten überschreiben Vorgabe', 'Anwählen, wenn Sie möchten, dass Varianten in diesem MetaModel die Werte der Elterntabelle überschreiben');

$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique']            = array('Eindeutig', 'Wählen, wenn Sie sicherstellen möchten, dass jeder Wert nur ein einziges mal vorkommen darf.');
+$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_langcode']       = array('Sprache', 'Sprache');
+$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_value']          = array('Beschreibungstext', 'Beschreibungstext');
+


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['title_legend']         = 'Typ, Benennung und Grundkonfiguration des Attributes';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['advanced_legend']      = 'Erweiterte Einstellungen';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['new']                  = array('Neues Attribut', 'Ein neues Attribut erstellen.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit']                 = array('Attribut bearbeiten', 'Das Attribut ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut']                  = array('Attribut-Definition ausschneiden', 'Schneiden Sie die Definition für das Attribut ID %s aus');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy']                 = array('Attribut-Definition kopieren', 'Kopieren Sie die Definition für das Attribut ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete']               = array('Attribut löschen', 'Löschen Sie die Definition für das Attribut ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show']                 = array('Attributdetails', 'Die Details von Attribut ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['editheader']           = array('Attribut bearbeiten', 'bearbeiten Sie das Attribut');

?>