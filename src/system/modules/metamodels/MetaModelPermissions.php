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
 * This class provides functions to check if a certain user or member has access to a MetaModel.
 * 
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelPermissions
{

	/**
	 * Checks if the given user has access to the MetaModel with the given id.
	 * 
	 * @param User $objUser      the user that shall be tested.
	 * @param int  $intMetaModelId the id of the MetaModel that the user shall be tested against.
	 * 
	 * @return bool true if the user has access to the MetaModel, false otherwise.
	 */
	public static function hasUserAccessTo(User $objUser, $intMetaModelId)
	{
		if ($objUser->isAdmin)
		{
			return true;
		}

		// is there any MetaModel setup for this user?
		if (!is_array($objUser->metamodels) || count($objUser->metamodels) == 0)
		{
			return false;
		}

		// MetaModel mentioned as allowed?
		return in_array($intMetaModelId, $objUser->metamodels);
	}

	/**
	 * Check permissions to table tl_metamodel.
	 * 
	 * @param User $objUser the user object for the current user.
	 * @param string $strAction the action the user want's to perform (standard DC_Table action parameters). Optional.
	 * @param int $intId the Id of the MetaModel to which the action shall be applied. Optional.
	 */
	public static function checkPermission(User $objUser, $strAction='', $intId=0)
	{
		if ($objUser->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($objUser->metamodels) || count($objUser->metamodels) < 1)
		{
			$root = array(0);
		}
		else
		{
			$root = $objUser->metamodels;
		}

		// restrict the metamodel table
		$GLOBALS['TL_DCA']['tl_metamodel']['config']['closed'] = true;
		$GLOBALS['TL_DCA']['tl_metamodel']['list']['sorting']['root'] = $root;

		// Check current action
		switch ($strAction)
		{
			case 'select':
				// Allow
				break;

			case 'edit':
			case 'show':
				if (!in_array($intId, $root))
				{
					$this->log('Not enough permissions to '.$strAction.' metamodel type ID "'.$intId.'"', 'tl_metamodel checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
				$this->Session->setData($session);
				break;

			default:
				if (strlen($this->Input->get('act')))
				{
					$this->log('Not enough permissions to '.$strAction.' metamodel types', 'tl_metamodel checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;
		}
	}
}
