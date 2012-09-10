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
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id']         = array('Attribut', 'Attribut, auf das sich diese Einstellung bezieht.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template']        = array('Eigenes Template für die Anzeige benutzen.', 'Wählen Sie das Template aus, das für das ausgewählte Attribut verwendet werden soll. Gültige templatenamen beginnen mit &quot;mm_<type>&quot;, wobei sich der Typname aus &lt;type&gt; ableitet.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['title_legend']    = 'Typ';
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['advanced_legend'] = 'Erweitert';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['new']             = array('Neu', 'Neue Render-Einstellung erstellen.');

$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit']            = array('Render-Einstellung bearbeiten', 'Die Einstellung ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy']            = array('Filtereinstellung kopieren', 'Die Filtereinstellung ID %s kopieren.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete']          = array('Filereinstellung löschen', 'Die Filtereinstellung ID %s löschen.');
$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show']            = array('Details', 'Die Details der Filtereinstellung ID %s anzeigen-');

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['row']             = '%s <strong>%s</strong> <em>[%s]</em>';

?>