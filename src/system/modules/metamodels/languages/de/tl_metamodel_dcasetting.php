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
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype']         = array('Typ', 'Den Attribut-Typ auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id']         = array('Attribute', 'Attribute, auf die sich diese Einstellung bezieht.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template']        = array('Eigenes Template für die Darstellung', 'Ein Template auswählen, das für das ausgewählte Attribut benutzt werden soll. Gültige Templatenamen beginnen mit &quot;mm_<type>&quot; - type steht dabei für den &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class']        = array('Backend-Class', 'Hier können eine oder mehrere Backend-Classes festgelegt werden. Für bessere Bedienung ist die Benutzung des Stylepickers empfohlen.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['stylepicker']     = 'Stylepicker';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide']      = array('Legende verbergen','Die Legende standardmäßig verbergen.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle']     = array('Legend title','Here you can enter the legend title.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode']   = 'Sprache';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value']      = 'legenden-Titel';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory']       = array('Pflichtfeld', 'Auswählen, wenn dieses Attribut ein Pflichtfeld sein soll.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable']      = array('Filterbar', 'Auswählen, wenn dieses Attribut für die Filterung im Backend zur Verfügung stehen soll.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortable']        = array('Sortierbar', 'Auswählen, wenn dieses Atribut für die Sortierung im Backend zur Verfügung stehen soll.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable']      = array('Durchsuchbar', 'Auswählen, wenn dieses Attribut für die Suche im Backend zur Verfügung stehen soll.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['flag']            = array('Sortierung überschreiben', 'Falls die globalen Sortiervorgaben der Palette für dieses Item überschrieben werden sollen bitte hier auswählen.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['title_legend']    = 'Typ';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['advanced_legend'] = 'Experteneinstellungen';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new']             = array('Neu', 'Neue Einstellung erzeugen.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit']            = array('Einstellung bearbeiten', 'Die Einstellungen %s bearbeiten');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy']            = array('Einstellung kopieren', 'Die Einstellung ID %s kopieren');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete']          = array('Einstellung löschen', 'Die Einstellung ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show']            = array('Einstellungsdetails', 'Die Detials für die Einstellung ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall']          = array('Alle hinzufügen', 'Alle Attribute zur Palette hinzufügen');

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['row']             = '%s <strong>%s</strong> <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legend_row']      = '<div class="dca_palette">%s%s</div>';

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['legend']         = 'Legende';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['attribute']      = 'Attribut';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['1']     = 'Nach dem ersten Buchstaben aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['2']     = 'Nach dem ersten Buchstaben absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['3']     = 'Nach dem ersten "length" Buchstaben aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['4']     = 'Nach dem ersten "length" Buchstaben absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['5']     = 'Nach Tag aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['6']     = 'Nach Tag absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['7']     = 'Nach Monat aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['8']     = 'Nach Monat absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['9']     = 'Nach Jahr aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['10']    = 'Nach Monat absteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['11']    = 'Aufsteigend sortieren';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['12']    = 'Absteigend sortieren';


?>