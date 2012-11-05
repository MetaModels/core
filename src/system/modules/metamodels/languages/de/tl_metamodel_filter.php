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
$GLOBALS['TL_LANG']['tl_metamodel_filter']['name']                 = array('Name', 'Name des Filtereinstellung.');
$GLOBALS['TL_LANG']['tl_metamodel_filter']['tstamp']               = array('Aktualisierungsdatum', 'Datum und Zeit der letzen Aktualisierung.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_filter']['title_legend']         = 'Name';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_filter']['new']                  = array('Neu', 'Neue Filtereinstellung erstellen');
$GLOBALS['TL_LANG']['tl_metamodel_filter']['edit']                 = array('Filtereinstellung bearbeiten', 'Die Filtereinstellung ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_metamodel_filter']['copy']                 = array('Filtereinstellung kopieren', 'Die Filtereinstellung ID %s kopieren');
$GLOBALS['TL_LANG']['tl_metamodel_filter']['delete']               = array('Filtereinstellung löschen', 'Die Filtereinstellung ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_filter']['show']                 = array('Filterdetails', 'Die Details der Filtereinstellung ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_metamodel_filter']['settings']             = array('Filterattribute erstellen', 'Filterattribute für Filtereinstellung ID %s definieren');

?>