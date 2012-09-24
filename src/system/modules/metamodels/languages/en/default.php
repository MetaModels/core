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
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MOD']['metamodels'] = array('MetaModels', 'The MetaModels extension allows you to create own data models.');

$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord'] = 'edit filter setting %%s for filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label'] = 'Filter "%s" in MetaModel "%s"';

$GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'] = 'Edit "%s" for Item %%s';

$GLOBALS['TL_LANG']['MSC']['no_palette'] = 'Attempt to access the metamodel "%s" without palette for current user %s.';
$GLOBALS['TL_LANG']['MSC']['no_view'] = 'Attempt to access the metamodel "%s" without view for user %s.';

?>