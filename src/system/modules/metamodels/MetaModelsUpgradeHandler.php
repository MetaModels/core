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
 * Upgrade handler class that changes structural changes in the database.
 * This should rarely be neccessary but sometimes we need it.
 */
class MetaModelsUpgradeHandler
{
	/**
	 * retrieve the database instance from Contao.
	 *
	 * @return Database
	 */
	protected static function DB()
	{
		// TODO: do we need to ensure the existance of the Contao object stack before somehow?
		return Database::getInstance();
	}

	/**
	 * Handle database upgrade for the jumpTo field.
	 *
	 * Introduced: pre release 1.0.
	 *
	 * If the field 'metamodel_jumpTo' does exist in tl_module or tl_content,
	 * it will get created and the content from jumpTo will get copied over.
	 *
	 * @return void
	 */
	protected static function upgradeJumpTo()
	{
		$objDB = self::DB();
		if (!$objDB->fieldExists('metamodel_jumpTo', 'tl_content', true))
		{
			// create the column in the database and copy the data over.
			MetaModelTableManipulation::createColumn(
				'tl_content',
				'metamodel_jumpTo',
				'int(10) unsigned NOT NULL default \'0\''
			);
			$objDB->execute('UPDATE tl_content SET metamodel_jumpTo=jumpTo;');
		}
		if (!$objDB->fieldExists('metamodel_jumpTo', 'tl_module', true))
		{
			// create the column in the database and copy the data over.
			MetaModelTableManipulation::createColumn(
				'tl_module',
				'metamodel_jumpTo',
				'int(10) unsigned NOT NULL default \'0\''
			);
			$objDB->execute('UPDATE tl_module SET metamodel_jumpTo=jumpTo;');
		}
	}

	/**
	 * Perform all upgrade steps.
	 *
	 * @return void
	 */
	public static function perform()
	{
		self::upgradeJumpTo();
	}
}

