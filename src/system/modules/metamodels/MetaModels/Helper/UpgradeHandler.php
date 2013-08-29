<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Helper;

/**
 * Upgrade handler class that changes structural changes in the database.
 * This should rarely be necessary but sometimes we need it.
 */
class UpgradeHandler
{
	/**
	 * retrieve the database instance from Contao.
	 *
	 * @return \Database
	 */
	protected static function DB()
	{
		// TODO: do we need to ensure the existance of the Contao object stack before somehow?
		return \Database::getInstance();
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
			TableManipulation::createColumn(
				'tl_content',
				'metamodel_jumpTo',
				'int(10) unsigned NOT NULL default \'0\''
			);
			$objDB->execute('UPDATE tl_content SET metamodel_jumpTo=jumpTo;');
		}
		if (!$objDB->fieldExists('metamodel_jumpTo', 'tl_module', true))
		{
			// create the column in the database and copy the data over.
			TableManipulation::createColumn(
				'tl_module',
				'metamodel_jumpTo',
				'int(10) unsigned NOT NULL default \'0\''
			);
			$objDB->execute('UPDATE tl_module SET metamodel_jumpTo=jumpTo;');
		}
	}

	/**
	 * Handle database upgrade for the published field in tl_metamodel_dcasetting.
	 *
	 * Introduced: version 1.0.1
	 *
	 * If the field 'published' does not exist in tl_metamodel_dcasetting,
	 * it will get created and all rows within that table will get initialized to 1
	 * to have the prior behaviour back (everything was being published before then).
	 *
	 * @return void
	 */
	protected static function upgradeDcaSettingsPublished()
	{
		$objDB = self::DB();
		if (!$objDB->fieldExists('published', 'tl_metamodel_dcasetting', true))
		{
			// create the column in the database and copy the data over.
			TableManipulation::createColumn(
				'tl_metamodel_dcasetting',
				'published',
				'char(1) NOT NULL default \'\''
			);
			// Publish everything we had so far.
			$objDB->execute('UPDATE tl_metamodel_dcasetting SET published=1;');
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
		self::upgradeDcaSettingsPublished();
	}
}
