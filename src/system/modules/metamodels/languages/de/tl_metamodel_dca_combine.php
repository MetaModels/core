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
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner']        = array('Zugriffsrechte für Paletten und Ansichten', 'Für ausgewählte Frontend-Mitgliedergruppen (wenn angegeben) und ausgewählte Backend-Benutzergrupp (falls angegeben) die ausgewählte Paöette und die ausgewählte Ansicht benutzenen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group']            = array('FE-Gruppe', 'Die Frontend-Gruppe auswählen, für die diese Kombination gilt.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group']            = array('BE-Gruppe', 'Die Backend-Gruppe auswählen, für die diese Kombination gilt.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id']              = array('Palette wählen', 'Die entsprechende Pallette auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id']             = array('Ansicht wählen', 'Die entsprechende Ansicht auswählen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['sysadmin']            = 'Administrator';

?>