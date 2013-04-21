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
 * Controller class for DC_General
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralControllerMetaModel extends GeneralControllerDefault
{

	/**
	 * Create a variant of the model currently loaded.
	 *
	 * @param DC_General $objDC the data container holding the current model
	 *
	 * @return void
	 */
	public function createvariant(DC_General $objDC)
	{
		// Check if table is editable
		if (!$objDC->isEditable())
		{
			$this->log('Table ' . $objDC->getTable() . ' is not editable', 'DC_General - Controller - edit()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Load fields and co
		$objDC->loadEditableFields();
		$objDC->setWidgetID($objDC->getId());

		// Check if we have fields
		if (!$objDC->hasEditableFields())
		{
			$this->redirect($this->getReferer());
		}

		// Load something
		$objDC->preloadTinyMce();

		// Set buttons
		$objDC->addButton("save");
		$objDC->addButton("saveNclose");

		// Load record from data provider
		$objDBModel = $objDC->getDataProvider()->createVariant($objDC->getDataProvider()->getEmptyConfig()->setId($objDC->getId()));
		if ($objDBModel == null)
		{
			$objDBModel = $objDC->getDataProvider()->getEmptyModel();
		}
		$objDC->setCurrentModel($objDBModel);

		// Check if we have a auto submit
		if ($objDC->isAutoSubmitted())
		{
			// process input and update changed properties.
			foreach (array_keys($objDC->getFieldList()) as $key)
			{
				$varNewValue = $objDC->processInput($key);
				if ($objDBModel->getProperty($key) != $varNewValue)
				{
					$objDBModel->setProperty($key, $varNewValue);
				}
			}

			$objDC->setCurrentModel($objDBModel);
		}

		// Check submit
		if ($objDC->isSubmitted() == true)
		{
			if (isset($_POST["save"]))
			{
				$this->getDC()->updateModelFromPOST();

				// process input and update changed properties.
				if ($this->doSave($objDC) !== false)
				{
					// Callback
					$objDC->getCallbackClass()->oncreateCallback($objDBModel->getID(), $objDBModel->getPropertiesAsArray());
					// Log
					$this->log('A new entry in table "' . $objDC->getTable() . '" has been created (ID: ' . $objDBModel->getID() . ')', 'DC_General - Controller - createvariant()', TL_GENERAL);
					// Redirect
					$this->redirect($this->addToUrl("id=" . $objDBModel->getID() . "&amp;act=edit"));
				}
			}
			else if (isset($_POST["saveNclose"]))
			{
				$this->getDC()->updateModelFromPOST();

				// process input and update changed properties.
				if ($this->doSave($objDC) !== false)
				{
					setcookie('BE_PAGE_OFFSET', 0, 0, '/');

					$_SESSION['TL_INFO']    = '';
					$_SESSION['TL_ERROR']   = '';
					$_SESSION['TL_CONFIRM'] = '';

					$this->redirect($this->getReferer());
				}
			}

			// Maybe Callbacks ?
		}
	}

	protected function doSave() {
		$objDBModel = $this->getDC()->getCurrentModel();

		// Check if table is closed
		if ($this->getDC()->arrDCA['config']['closed'] && !($objDBModel->getID()))
		{
			// TODO show alarm message
			$this->redirect($this->getReferer());
		}

		// if we may not store the value, we keep the changes
		// in the current model and return (DO NOT SAVE!).
		if ($this->getDC()->isNoReload() == true)
		{
			return false;
		}

		$this->getDC()->getCallbackClass()->onsubmitCallback();
		$objDBModel->setProperty("tstamp", time());
		$this->getDC()->getCallbackClass()->onsaveCallback($objDBModel);

		//        $this->getNewPosition($objDBModel, 'create', null, false);
		// everything went ok, now save the new record
		if (!$objDBModel->getMeta(DCGE::MODEL_IS_CHANGED))
		{
			return $objDBModel;
		}

		try {
			$this->getDC()->getDataProvider()->save($objDBModel);
		} catch(MetaModelItemNotSaveableException $e) {
			$this->addErrorMessage($e->getMessage());
			return false;
		}

		// Return the current model
		return $objDBModel;
	}

}

