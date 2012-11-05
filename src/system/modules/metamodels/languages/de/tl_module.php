<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['mm_filter_legend']				= 'MetaModel Filter';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['metamodel']                = array('MetaModel', 'Das MetaModel angeben, nach dem aufgelistet werden soll.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit']      = array('Datensätze überspringen und begrenzen', 'Auswählen, um die Anzahl der aufgelisteten Datensätze zu begrenzen. Die Einstellung wird benötigt, um beispielsweise die 500 ersten Datensätze oder alle mit Ausnahme der ersten 10 Datensätze aufzulisten und dabei eine korrekte Paginierung zu ermöglichen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_offset']         = array('Datensätze überspringen', 'Bitte die Anzahl der Datensätze angeben, die übersprungen werden sollen (zum Beispiel 10, um die ersten 10 Datensätze zu überspringen).');
$GLOBALS['TL_LANG']['tl_module']['metamodel_limit']          = array('Maximale Anzahl an Datensätzen', 'Bitte die maximale Zahl der anzuzeigenden Datensätze angeben. Um alle Datensätze anzuzeigen und die Paginierung auszuschalten den Wert '0' eingeben.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby']         = array('Sortieren nach', 'Bitte die Reihenfolge für die Sortierung auswählen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby_direction'] = array('Sortierreihenfolge', 'Austeigende oder absteigende Reihenfolge.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering']      = array('Anzuwendende Filtereinstellungen', 'Die Filtereinstellungen auswählen, die beim Zusammenstellen der Datensatzliste angewandt werden sollen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_layout']         = array('Eigenes Template für Datensatzliste auswählen', 'Das Template auswählen, das für die Generierung der Datensatzliste mit den ausgewählten Attributen benutzt werden soll. Gültige Templatenamen beginnen mit &quot;mod_metamodel_&lt;type&gt;&quot;, wobei 'type' für den jeweiligen &lt;Typ&gt; steht.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'] = array('Anzuwendende Rendereinstellungen', 'Die Rendereinstellungen auswählen, die benutzt werden sollen, um die Ausgabe zu erstellen. Falls leer werden die Standardeinstellungen für das ausgewählte MetaModel benutzt. Ist kein Standard definiert, dann werden Rohwerte ausgegeben.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams']			= array('Anzuwendende Filtereinstellungen', 'Auswählen, welche Filtereinstellungen benutzt werden sollen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams_use_get'] = array('GET Parameter zulassen', 'Diesem Parameter erlauben, von _GET-Parametern überschrieben zu werden.');


$GLOBALS['TL_LANG']['tl_module']['metamodel_noparsing']      = array('Keine Ausgabe von Datensätzen', 'Wenn ausgewählt wird das Modul keine Datensätze ausgeben. Nur die entsprechenden Datensatzobjekte werden dem Template übergeben.');
/**
 * Wizards
 */

$GLOBALS['TL_LANG']['tl_module']['editmetamodel']            = array('MetaModel bearbeiten', 'Das Metamodel ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_module']['editrendersetting']        = array('Darstellungsoptionen bearbeiten', 'Die Darstellungsoptionen von ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_module']['editfiltersetting']        = array('Filtereinstellung bearbeiten', 'Die Filtereinstellung von ID %s bearbeiten.');


?>