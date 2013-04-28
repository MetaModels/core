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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['mm_filter_legend']				= 'MetaModel Filter';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['metamodel']                = array('MetaModel', 'Das MetaModel angeben, nach dem aufgelistet werden soll.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit']      = array('Datensätze überspringen und begrenzen', 'Auswählen, um die Anzahl der aufgelisteten Datensätze zu begrenzen. Die Einstellung wird benötigt, um beispielsweise die 500 ersten Datensätze oder alle mit Ausnahme der ersten 10 Datensätze aufzulisten und dabei eine korrekte Paginierung zu ermöglichen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_offset']         = array('Datensätze überspringen', 'Bitte die Anzahl der Datensätze angeben, die übersprungen werden sollen (z.B. "10", um die ersten 10 Datensätze zu überspringen).');
$GLOBALS['TL_LANG']['tl_module']['metamodel_limit']          = array('Maximale Anzahl an Datensätzen', 'Bitte die maximale Zahl der anzuzeigenden Datensätze angeben. Um alle Datensätze anzuzeigen und die Paginierung auszuschalten den Wert "0" eingeben.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby']         = array('Sortieren nach', 'Bitte die Reihenfolge für die Sortierung auswählen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby_direction'] = array('Sortierreihenfolge', 'Aufsteigende oder absteigende Reihenfolge.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering']      = array('Anzuwendende Filtereinstellungen', 'Die Filtereinstellungen auswählen, die beim Zusammenstellen der Datensatzliste angewandt werden sollen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_layout']         = array('Eigenes Template für Datensatzliste auswählen', 'Das Template auswählen, das für die Generierung der Datensatzliste mit den ausgewählten Attributen benutzt werden soll. Gültige Templatenamen beginnen mit "mod_metamodel_&lt;type&gt;", wobei &lt;type&gt; für den jeweiligen &lt;Typ&gt; steht.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'] = array('Anzuwendende Rendereinstellungen', 'Die Rendereinstellungen auswählen, die benutzt werden sollen, um die Ausgabe zu erstellen. Falls leer, werden die Standardeinstellungen für das ausgewählte MetaModel benutzt. Ist kein Standard definiert, werden Rohwerte ausgegeben.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_autosubmit']		= array('Automatische Aktualisierung', 'Filter bei jeder Änderung aktualisieren.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_params']			= array('Filterparameter', 'In diesem FE-Filter angezeigte Parameter Widgets auswählen.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_template']			= array('Template', 'Frontend-Template auswählen.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_jumpTo']		= array('Weiterleitungsseite', 'Bitte wählen Sie die Seite aus, zu der Besucher beim Anklicken eines Links oder Abschicken eines Formulars weitergeleitet werden.');

/**
 * Wizards
 */

$GLOBALS['TL_LANG']['tl_module']['editmetamodel']            = array('MetaModel bearbeiten', 'Das MetaModel ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_module']['editrendersetting']        = array('Darstellungsoptionen bearbeiten', 'Die Darstellungsoptionen von ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_module']['editfiltersetting']        = array('Filtereinstellung bearbeiten', 'Die Filtereinstellung von ID %s bearbeiten.');

