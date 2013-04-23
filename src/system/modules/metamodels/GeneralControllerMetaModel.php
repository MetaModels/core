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
	 * @param DC_General $objDC The data container holding the current model.
	 *
	 * @return void
	 */
	public function createvariant(DC_General $objDC)
	{
		// Check if table is editable.
		if (!$objDC->isEditable())
		{
			$this->log('Table ' . $objDC->getTable() . ' is not editable', 'DC_General - Controller - edit()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Load fields.
		$objDC->loadEditableFields();
		$objDC->setWidgetID($objDC->getId());

		// Check if we have fields.
		if (!$objDC->hasEditableFields())
		{
			$this->redirect($this->getReferer());
		}

		// Load rich text editor.
		$objDC->preloadTinyMce();

		// Set buttons.
		$objDC->addButton('save');
		$objDC->addButton('saveNclose');

		// Load record from data provider.
		$objDBModel = $objDC
			->getDataProvider()
			->createVariant(
				$objDC
					->getDataProvider()
					->getEmptyConfig()
					->setId($objDC->getId())
			);
		if ($objDBModel == null)
		{
			$objDBModel = $objDC->getDataProvider()->getEmptyModel();
		}
		$objDC->setCurrentModel($objDBModel);

		// Check if we have a auto submit.
		if ($objDC->isAutoSubmitted())
		{
			// Process input and update changed properties.
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

		// Check submit.
		if ($objDC->isSubmitted() == true)
		{
			// @codingStandardsIgnoreStart - we know that access to $_POST is discouraged.
			if (isset($_POST['save']))
			// @codingStandardsIgnoreEnd
			{
				$this->getDC()->updateModelFromPOST();

				// Process input and update changed properties.
				if ($this->doSave($objDC) !== false)
				{
					// Call the on create callbacks.
					$objDC
						->getCallbackClass()
						->oncreateCallback($objDBModel->getID(), $objDBModel->getPropertiesAsArray());
					// Log the creation.
					$this->log(
						sprintf(
							'A new entry in table "%s" has been created (ID: %s)',
							$objDC->getTable(),
							$objDBModel->getID()
						),
						'DC_General - Controller - createvariant()',
						TL_GENERAL
					);
					// Redirect to edit mode.
					$this->redirect($this->addToUrl('id=' . $objDBModel->getID() . '&amp;act=edit'));
				}
			}
			// @codingStandardsIgnoreStart - we know that access to $_POST is discouraged.
			elseif (isset($_POST['saveNclose']))
			// @codingStandardsIgnoreEnd
			{
				$this->getDC()->updateModelFromPOST();

				// Process input and update changed properties.
				if ($this->doSave($objDC) !== false)
				{
					setcookie('BE_PAGE_OFFSET', 0, 0, '/');

					// @codingStandardsIgnoreStart - we know that access to $_SESSION is discouraged.
					$_SESSION['TL_INFO']    = '';
					$_SESSION['TL_ERROR']   = '';
					$_SESSION['TL_CONFIRM'] = '';
					// @codingStandardsIgnoreEnd

					$this->redirect($this->getReferer());
				}
			}
			// Maybe Callbacks?
		}
	}
}

