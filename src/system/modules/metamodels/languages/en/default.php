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
$GLOBALS['TL_LANG']['MOD']['metamodel'] = array('MetaModel', 'The MetaModels extension allows you to create own data models.');


//Select Options
$GLOBALS['TL_LANG']['MSC']['optionsTitle'] = 'Select %s';

$GLOBALS['TL_LANG']['MSC']['noCatalog'] = 'Catalog doesn\'t exist, contact the administrator.';
$GLOBALS['TL_LANG']['MSC']['removeDataConfirm'] = 'Do you really want to delete all records from %s before importing?';


/**
 * Error
 */
$GLOBALS['TL_LANG']['ERR']['noHeaderFields']     = 'The header fields (line 1) in the CSV file must exactly match those defined in the catalog';
$GLOBALS['TL_LANG']['ERR']['noCSVData']     = 'There is no data in the CSV file.';
$GLOBALS['TL_LANG']['ERR']['importSuccess'] = 'CSV import to the catalog successful: %s records';
$GLOBALS['TL_LANG']['ERR']['noCSVFile']     = 'Please select a CSV file!';
$GLOBALS['TL_LANG']['ERR']['filetype']       = 'File type "%s" is not allowed to be uploaded!';
$GLOBALS['TL_LANG']['ERR']['filepartial']    = 'File %s was only partially uploaded!';
$GLOBALS['TL_LANG']['ERR']['importFolder']   = 'Folder "%s" cannot be imported!';

$GLOBALS['TL_LANG']['ERR']['tableExists'] = 'Table %s already exists. Please choose different name.';
$GLOBALS['TL_LANG']['ERR']['tableDoesNotExist'] = 'Table %s does not exists.';
$GLOBALS['TL_LANG']['ERR']['columnExists'] = 'Column %s already exists. Please choose different name.';
$GLOBALS['TL_LANG']['ERR']['columnDoesNotExist'] = 'Column %s does not exist in table %s.';
$GLOBALS['TL_LANG']['ERR']['systemColumn'] = 'Name %s is reserved for system use. Please choose different name.';
$GLOBALS['TL_LANG']['ERR']['invalidColumnName'] = 'Invalid column name. Please use only letters, numbers and underscore.';
$GLOBALS['TL_LANG']['ERR']['invalidTableName'] = 'Invalid table name. Please use only letters, numbers and underscore.';


$GLOBALS['TL_LANG']['ERR']['aliasTitleMissing'] = 'Incorrect alias field configuration. Missing Title field parameter.';
$GLOBALS['TL_LANG']['ERR']['aliasDuplicate'] = 'Alias field `%s` already defined. Only one alias field is allowed per table.';

$GLOBALS['TL_LANG']['ERR']['limitMin'] = 'This value is smaller than the minimum value: %s';
$GLOBALS['TL_LANG']['ERR']['limitMax'] = 'This value is greater than the maximum value: %s';

$GLOBALS['TL_LANG']['ERR']['calcInvalid'] = 'Invalid Calculation SQL statement: %s';
$GLOBALS['TL_LANG']['ERR']['calcError'] = 'Calculation Error - %s';

$GLOBALS['TL_LANG']['ERR']['catalogItemInvalid'] = 'Catalog Item not Found';
$GLOBALS['TL_LANG']['MSC']['catalogItemEditingDenied'] = 'You are not allowed to edit this item.';

/**
 * Filter Module
 */

$GLOBALS['TL_LANG']['MSC']['catalogSearch'] = 'Go';
$GLOBALS['TL_LANG']['MSC']['catalogSearchResults'] = 'Results %u - %u of %u';
$GLOBALS['TL_LANG']['MSC']['catalogSearchPages'] = '(page %u of %u)';
$GLOBALS['TL_LANG']['MSC']['catalogSearchEmpty'] = 'No matches for your search';
$GLOBALS['TL_LANG']['MSC']['clearFilter'] = 'Clear all filters';
$GLOBALS['TL_LANG']['MSC']['clearAll'] = 'Clear %s'; // %s=field label
$GLOBALS['TL_LANG']['MSC']['selectNone'] = 'Select %s'; // %s=field label
$GLOBALS['TL_LANG']['MSC']['optionselected'] 	= '%s'; // %s=field label
$GLOBALS['TL_LANG']['MSC']['invalidFilter'] = 'Invalid filter type';
$GLOBALS['TL_LANG']['MSC']['rangeFrom'] = 'from';
$GLOBALS['TL_LANG']['MSC']['rangeTo'] = 'to';


// Checkbox options
$GLOBALS['TL_LANG']['MSC']['true'] = 'Yes';
$GLOBALS['TL_LANG']['MSC']['false'] = 'No';


// Date options
$GLOBALS['TL_LANG']['MSC']['daterange']['y'] = 'Last year';
$GLOBALS['TL_LANG']['MSC']['daterange']['h'] = 'Last 6 months';
$GLOBALS['TL_LANG']['MSC']['daterange']['m'] = 'Last month';
$GLOBALS['TL_LANG']['MSC']['daterange']['w'] = 'Last week';
$GLOBALS['TL_LANG']['MSC']['daterange']['d'] = 'Yesterday';
$GLOBALS['TL_LANG']['MSC']['daterange']['t'] = 'Today';
$GLOBALS['TL_LANG']['MSC']['daterange']['df'] = 'Tomorrow';
$GLOBALS['TL_LANG']['MSC']['daterange']['wf'] = 'Next week';
$GLOBALS['TL_LANG']['MSC']['daterange']['mf'] = 'Next month';
$GLOBALS['TL_LANG']['MSC']['daterange']['hf'] = 'Next 6 months';
$GLOBALS['TL_LANG']['MSC']['daterange']['yf'] = 'Next year';

// Sort options
$GLOBALS['TL_LANG']['MSC']['unsorted'] 	= 'Select Order';
$GLOBALS['TL_LANG']['MSC']['lowhigh'] = '(Low to High)';
$GLOBALS['TL_LANG']['MSC']['highlow'] = '(High to Low)';
$GLOBALS['TL_LANG']['MSC']['AtoZ'] 		= '(A-Z)';
$GLOBALS['TL_LANG']['MSC']['ZtoA'] 		= '(Z-A)';
$GLOBALS['TL_LANG']['MSC']['truefalse'] = '(True-False)';
$GLOBALS['TL_LANG']['MSC']['falsetrue'] = '(False-True)';
$GLOBALS['TL_LANG']['MSC']['dateasc'] 	= '(Oldest First)';
$GLOBALS['TL_LANG']['MSC']['datedesc'] 	= '(Recent First)';


/**
 * List Module
 */

$GLOBALS['TL_LANG']['MSC']['viewCatalog']     = 'View the item details';
$GLOBALS['TL_LANG']['MSC']['editCatalog']     = 'Edit the item details';

/**
 * Notify Module
 */

$GLOBALS['TL_LANG']['MSC']['notifySubmit']	= 'Send Notification';
$GLOBALS['TL_LANG']['MSC']['notifyConfirm']	= 'Your notification has been sent.';
$GLOBALS['TL_LANG']['MSC']['notifyMessage']	= 'Message';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['noItemsMsg'] = 'No entry that matches the conditions has been found. You can customize this message using <strong>$GLOBALS[\'TL_LANG\'][\'MSC\'][\'noItemsMsg\'] = \'My message\';</strong> in your system/config/langconfig.php';
$GLOBALS['TL_LANG']['MSC']['catalogCondition']	= 'Please first select the following filter(s): %s';
$GLOBALS['TL_LANG']['MSC']['catalogInvalid'] 		= 'Invalid Catalog!';
$GLOBALS['TL_LANG']['MSC']['catalogNoFields'] 	= 'No Catalog fields defined!';

$GLOBALS['TL_LANG']['MSC']['keywordsBlacklist'] = array(
	'the','i','me','you','he','she','it','we','you','they','their','his','her','its','a','us','there','out','on','over','at','before','after','as','and','or','from','for','with','without','are','is'
	);

$GLOBALS['TL_LANG']['MSC']['com_catalog_subject']  = 'Contao :: New comment in Catalog: %s [%s]';
$GLOBALS['TL_LANG']['MSC']['com_catalog_message']  = "Catalog Name: %s\nItem Title: %s\n\n%s has created a new comment on your website.\n\n---\n\n%s\n\n---\n\nView: %s\nEdit: %s\n\nIf you are moderating comments, you have to log in to the back end to publish it.";


/*
 * Frontend editing.
 */
$GLOBALS['TL_LANG']['MSC']['removeImage'] = 'Remove %s';

/**
 * Reporting
 */
$GLOBALS['TL_LANG']['MSC']['reportAbuse'] = 'Report abuse';


?>