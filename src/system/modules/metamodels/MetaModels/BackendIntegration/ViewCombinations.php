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
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\InputScreen\InputScreen;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged in user (either frontend or backend).
 *
 * @package MetaModels
 */
class ViewCombinations
{
	const COMBINATION   = 'combination';
	const INPUTSCREEN   = 'inputscreen';
	const RENDERSETTING = 'rendersetting';
	const MODELNAME     = 'metamodel';

	/**
	 * All MetaModel combinations for lookup.
	 *
	 * @var array
	 */
	protected static $information = array();

	/**
	 * All MetaModel combinations for lookup.
	 *
	 * @var array
	 */
	protected static $tableMap = array();

	/**
	 * Returns the proper user object for the current context.
	 *
	 * Returns:
	 * - the BackendUser when TL_MODE == 'BE',
	 * - the FrontendUser when TL_MODE == 'FE'
	 * - null otherwise
	 *
	 * @return \BackendUser|\FrontendUser|null
	 */
	protected static function getUser()
	{
		if (TL_MODE == 'BE')
		{
			return \BackendUser::getInstance();
		}
		elseif (TL_MODE == 'FE')
		{
			return \FrontendUser::getInstance();
		}
		return null;
	}

	/**
	 * Retrieve the Contao database singleton instance.
	 *
	 * @return \Database
	 */
	protected static function getDb()
	{
		return \Database::getInstance();
	}

	/**
	 * Initialize the class properties.
	 *
	 * @return void
	 */
	protected static function initialize()
	{
		$metaModels = self::getDb()
			->executeUncached('SELECT id, tableName FROM tl_metamodel order by sorting');

		while ($metaModels->next())
		{
			self::$information[$metaModels->id] = array
			(
				self::COMBINATION     => null,
				self::INPUTSCREEN     => null,
				self::RENDERSETTING   => null,
				self::MODELNAME       => $metaModels->tableName,
			);

			self::$tableMap[$metaModels->tableName] = $metaModels->id;
		}
	}

	/**
	 * Retrieve the user groups of the current user.
	 *
	 * @return array
	 */
	protected static function getUserGroups()
	{
		$user = self::getUser();
		// Try to get the group(s)
		// there might be a NULL in there as BE admins have no groups and user might have one but it is a not must have.
		// I would prefer a default group for both, fe and be groups.
		$groups = $user->groups ? array_filter($user->groups) : array();

		// Special case in combinations, admins have the implicit group id -1.
		if ((TL_MODE == 'BE') && $user->admin)
		{
			$groups[] = -1;
		}
		return $groups;
	}

	/**
	 * Fetch the palette view configurations valid for the given group ids.
	 *
	 * This returns the ids that have not been resolved as no combination has been defined and therefore the default
	 * must be used for.
	 *
	 * @return int[]
	 */
	protected static function getPaletteCombinationRows()
	{
		$groupColumn = (TL_MODE == 'BE') ? 'be_group' : 'fe_group';
		$groups      = self::getUserGroups();

		if (!$groups)
		{
			return array();
		}

		$objCombinations = self::getDb()
			->prepare(sprintf(
				'SELECT * FROM tl_metamodel_dca_combine WHERE %s IN (%s) ORDER BY pid,sorting ASC',
				$groupColumn,
				implode(',', $groups)
			))
			->executeUncached();

		$success = array();

		while ($objCombinations->next())
		{
			// Already a combination present, continue with next one.
			if (!empty(self::$information[$objCombinations->pid][self::COMBINATION]))
			{
				continue;
			}
			self::$information[$objCombinations->pid][self::COMBINATION] = $objCombinations->row();

			$success[] = $objCombinations->pid;
		}

		return array_diff(array_keys(self::$information), $success);
	}

	/**
	 * Get the default combination of palette and view (if any has been defined).
	 *
	 * @param int[] $metaModelIds The MetaModels for which combinations shall be retrieved.
	 *
	 * @return void
	 */
	protected static function getPaletteCombinationDefault($metaModelIds)
	{
		$inputScreen = self::getDb()
			->prepare(sprintf(
				'SELECT * FROM tl_metamodel_dca WHERE pid IN (%s) AND isdefault=1',
				implode(',', $metaModelIds)
			))
			->executeUncached();

		while ($inputScreen->next())
		{
			self::$information[$inputScreen->pid][self::COMBINATION]['dca_id'] = $inputScreen->id;
		}

		$renderSetting = self::getDb()
			->prepare(sprintf(
				'SELECT * FROM tl_metamodel_rendersettings WHERE pid IN (%s) AND isdefault=1',
				implode(',', $metaModelIds)
			))
			->executeUncached();

		while ($renderSetting->next())
		{
			self::$information[$renderSetting->pid][self::COMBINATION]['view_id'] = $renderSetting->id;
		}
	}

	/**
	 * Pull in all DCA settings for the buffered MetaModels and buffer them in the static class.
	 *
	 * @return void
	 */
	protected static function fetchInputScreenDetails()
	{
		$inputScreenIds = array();
		foreach (self::$information as $info)
		{
			if (!empty($info[self::COMBINATION]['dca_id']))
			{
				$inputScreenIds[] = $info[self::COMBINATION]['dca_id'];
			}
		}

		if (!$inputScreenIds)
		{
			return;
		}

		$inputScreens = self::getDb()
			->prepare(sprintf(
				'SELECT * FROM tl_metamodel_dca WHERE id IN (%s)',
				implode(',', $inputScreenIds)
			))
			->executeUncached();

		if (!$inputScreens->numRows)
		{
			return;
		}

		while ($inputScreens->next())
		{
			$propertyRows = self::getDb()
				->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND published=1 ORDER BY sorting ASC')
				->executeUncached($inputScreens->id);

			$conditions = self::getDb()
				->prepare('
					SELECT cond.*, setting.attr_id AS setting_attr_id
					FROM tl_metamodel_dcasetting_condition AS cond
					LEFT JOIN tl_metamodel_dcasetting AS setting
					ON (cond.settingId=setting.id)
					LEFT JOIN tl_metamodel_dca AS dca
					ON (setting.pid=dca.id)
					WHERE dca.id=? AND setting.published=1 AND cond.enabled=1
					ORDER BY sorting ASC
				')
				->executeUncached($inputScreens->id);

			self::$information[$inputScreens->pid][self::INPUTSCREEN] = new InputScreen(
				$inputScreens->row(),
				$propertyRows->fetchAllAssoc(),
				$conditions->fetchAllAssoc()
			);
		}
	}

	/**
	 * Pull in all DCA settings for the buffered MetaModels and buffer them in the static class.
	 *
	 * @return void
	 */
	protected static function fetchRenderSettingDetails()
	{
		$renderSettingIds = array();
		foreach (self::$information as $info)
		{
			if (!empty($info[self::COMBINATION]['view_id']))
			{
				$renderSettingIds[] = $info[self::COMBINATION]['view_id'];
			}
		}

		if (!$renderSettingIds)
		{
			return;
		}

		$renderSettings = self::getDb()
			->prepare(sprintf(
				'SELECT * FROM tl_metamodel_rendersettings WHERE id IN (%s)',
				implode(',', $renderSettingIds)
			))
			->executeUncached();

		while ($renderSettings->next())
		{
			self::$information[$renderSettings->pid][self::RENDERSETTING] = $renderSettings->row();
		}
	}

	/**
	 * Collect information for all metamodels from the Database having an relation to the current user and buffer them.
	 *
	 * The metamodels are buffered in class local lists to have them handy when a corresponding parent table is being
	 * instantiated etc.
	 *
	 * @return void
	 */
	protected static function bufferModels()
	{
		if (!empty(self::$information))
		{
			return;
		}

		if (!\Database::getInstance()->tableExists('tl_metamodel', null))
		{
			return;
		}

		self::initialize();

		$fallback = self::getPaletteCombinationRows();

		if (!$fallback)
		{
			$fallback = array_keys(self::$information);
		}

		if ($fallback)
		{
			self::getPaletteCombinationDefault($fallback);
		}

		// Clean any undefined.
		foreach (array_keys(self::$information) as $id)
		{
			if (empty(self::$information[$id][self::COMBINATION]))
			{
				unset(self::$tableMap[self::$information[$id][self::MODELNAME]]);
				unset(self::$information[$id][self::COMBINATION]);
			}
		}

		self::fetchInputScreenDetails();
		self::fetchRenderSettingDetails();
	}

	/**
	 * Retrieve the render setting that is active for the current user.
	 *
	 * @param string|int $metaModel The name or id of the MetaModel.
	 *
	 * @return int
	 */
	public static function getRenderSetting($metaModel)
	{
		$renderSetting = self::getRenderSettingDetails($metaModel);

		return $renderSetting ? $renderSetting['id'] : null;
	}

	/**
	 * Retrieve the input screen that is active for the current user.
	 *
	 * @param string|int $metaModel The name or id of the MetaModel.
	 *
	 * @return int
	 */
	public static function getInputScreen($metaModel)
	{
		$inputScreen = self::getInputScreenDetails($metaModel);
		return $inputScreen ? $inputScreen->getId() : null;
	}

	/**
	 * Retrieve the input screen that is active for the current user.
	 *
	 * @param string|int $metaModel The name or id of the MetaModel.
	 *
	 * @return IInputScreen
	 */
	public static function getInputScreenDetails($metaModel)
	{
		self::bufferModels();

		if (!is_numeric($metaModel))
		{
			$metaModel = self::$tableMap[$metaModel];
		}

		return self::$information[$metaModel][self::INPUTSCREEN];
	}

	/**
	 * Retrieve the input screen that is active for the current user.
	 *
	 * @param string|int $metaModel The name or id of the MetaModel.
	 *
	 * @return array
	 */
	public static function getRenderSettingDetails($metaModel)
	{
		self::bufferModels();

		if (!is_numeric($metaModel))
		{
			$metaModel = self::$tableMap[$metaModel];
		}

		return self::$information[$metaModel][self::RENDERSETTING];
	}

	/**
	 * Retrieve all standalone input screens.
	 *
	 * @return IInputScreen[]
	 */
	public static function getStandaloneInputScreens()
	{
		self::bufferModels();

		$result = array();
		foreach (self::$information as $information)
		{
			/** @var IInputScreen $inputScreen */
			$inputScreen = isset($information[self::INPUTSCREEN]) ? $information[self::INPUTSCREEN] : null;
			if ($inputScreen && $inputScreen->isStandalone())
			{
				$result[] = $information[self::INPUTSCREEN];
			}
		}

		return $result;
	}

	/**
	 * Retrieve all standalone input screens.
	 *
	 * @return IInputScreen[]
	 */
	public static function getParentedInputScreens()
	{
		self::bufferModels();

		$result = array();
		foreach (self::$information as $information)
		{
			/** @var IInputScreen $inputScreen */
			$inputScreen = isset($information[self::INPUTSCREEN]) ? $information[self::INPUTSCREEN] : null;
			if ($inputScreen && !$inputScreen->isStandalone())
			{
				$result[] = $information[self::INPUTSCREEN];
			}
		}

		return $result;
	}
}
