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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['display_legend']		  = 'Anzeige-Einstellungen';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions']['tags']  = 'Tags';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tag_table']			  = array('Datenbank-Tabelle', 'Bitte die Datenbank-Tabelle auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tag_column']			  = array('Tabellenspalte', 'Bite die Spalte auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tag_id']				  = array('Tag ID', 'Bitte einen Eintrag für den Tag id auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tag_alias']			  = array('Tag-Alias', 'Bitte einen Eintrag für den Tag-Alias auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tag_sorting']    	  = array('Tag-Sortierung', 'Bitte einen Eintrag für die Tag-Sortierung auswählen.');

?>