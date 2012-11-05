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
 * @author Carolina M Koehn <ck@kikmedia.de>
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord'] 	= 'Bearbeiten der Filtereinstellungen %%s für den Filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label'] 			= 'Filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'] 			= '"%s" für Item %%s bearbeiten';
$GLOBALS['TL_LANG']['MSC']['sorting'] 									= 'Sortierung';

// Stylepicker
$GLOBALS['TL_LANG']['MSC']['tl_class']['w50']         					= array('w50', 'Die Breite auf 50% und nach links floaten.');
$GLOBALS['TL_LANG']['MSC']['tl_class']['clr']         					= array('clr', 'Alle Floats clearen.');
$GLOBALS['TL_LANG']['MSC']['tl_class']['m12']         					= array('m12', 'Dem Element einen oberen Abstand (top-margin) von 12 Pixel geben (für einzelne Checkboxen).');
$GLOBALS['TL_LANG']['MSC']['tl_class']['wizard']      					= array('wizard', 'Das Eingabefeld kürzen, so dass Platz für einen Wizard ist (z.B. einen Date-Picker).');
$GLOBALS['TL_LANG']['MSC']['tl_class']['long']        					= array('long', 'Vergrößert das Eingabefeld, so dass es zwei Spalten umfasst.');

// Panelpicker
+$GLOBALS['TL_LANG']['MSC']['panelLayout']['search']  					= array('Suche', '');
+$GLOBALS['TL_LANG']['MSC']['panelLayout']['sort']    					= array('Sortierung', '');
+$GLOBALS['TL_LANG']['MSC']['panelLayout']['filter']  					= array('Filter', '');
+$GLOBALS['TL_LANG']['MSC']['panelLayout']['limit']   					= array('Limitierung', '');
 
/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['no_attribute_extension'] = 'Bitte mindestens eine Erweiterung für Attribute installieren! MetaModels ohne Attribute sind sinnlos.';
$GLOBALS['TL_LANG']['ERR']['activate_extension'] = 'Bitte die benötigte Erweiterung &quot;%s&quot; (%s) aktivieren.';
$GLOBALS['TL_LANG']['ERR']['install_extension'] = 'Bitte die benötigte Erweiterung &quot;%s&quot; (%s) installieren.';
$GLOBALS['TL_LANG']['MSC']['no_palette'] = 'Versuch, auf MetaModel "%s" ohne Palette für den Benutzer %s zuzugreifen.';
$GLOBALS['TL_LANG']['MSC']['no_view'] = 'Versuch, auf das MetaModel "%s" ohne Ansicht für den Benutzer %s zuzugeifen.';



?>