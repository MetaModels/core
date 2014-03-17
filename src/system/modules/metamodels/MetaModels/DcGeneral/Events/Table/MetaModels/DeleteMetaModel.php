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

namespace MetaModels\DcGeneral\Events\Table\MetaModels;

use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use MetaModels\Factory;
use MetaModels\Helper\TableManipulation;
use MetaModels\IMetaModel;

/**
 * Handles delete operation on tl_metamodel.
 *
 * @package MetaModels\DcGeneral\Events\Table\MetaModels
 */
class DeleteMetaModel
{
	/**
	 * Destroy auxiliary data of attributes and delete the attributes themselves from the database.
	 *
	 * @param IMetaModel $metaModel The MetaModel to destroy.
	 *
	 * @return void
	 */
	protected static function destroyAttributes(IMetaModel $metaModel)
	{
		foreach ($metaModel->getAttributes() as $attribute)
		{
			$attribute->destroyAUX();
		}

		\Database::getInstance()
			->prepare('DELETE FROM tl_metamodel_attribute WHERE pid=?')
			->executeUncached($metaModel->get('id'));
	}

	/**
	 * Destroy the dca combinations for a MetaModel.
	 *
	 * @param IMetaModel $metaModel The MetaModel to destroy.
	 *
	 * @return void
	 */
	protected static function destroyDcaCombinations(IMetaModel $metaModel)
	{
		\Database::getInstance()
			->prepare('DELETE FROM tl_metamodel_dca_combine WHERE pid=?')
			->executeUncached($metaModel->get('id'));
	}

	/**
	 * Destroy the input screens for a MetaModel.
	 *
	 * @param IMetaModel $metaModel The MetaModel to destroy.
	 *
	 * @return void
	 */
	protected static function destroyInputScreens(IMetaModel $metaModel)
	{
		// Delete everything from dca settings.
		$arrIds = \Database::getInstance()
			->prepare('SELECT id FROM tl_metamodel_dca WHERE pid=?')
			->executeUncached($metaModel->get('id'))
			->fetchEach('id');

		if ($arrIds)
		{
			\Database::getInstance()
				->prepare(sprintf(
					'DELETE FROM tl_metamodel_dcasetting WHERE pid IN (%s)',
					implode(',', $arrIds))
				)
				->executeUncached();
		}

		// Delete the input screens.
		\Database::getInstance()->prepare('DELETE FROM tl_metamodel_dca WHERE pid=?')
			->executeUncached($metaModel->get('id'));
	}

	/**
	 * Destroy the render settings for a MetaModel.
	 *
	 * @param IMetaModel $metaModel The MetaModel to destroy.
	 *
	 * @return void
	 */
	protected static function destroyRenderSettings(IMetaModel $metaModel)
	{
		// Delete everything from render settings.
		$arrIds = \Database::getInstance()
			->prepare('SELECT id FROM tl_metamodel_rendersettings WHERE pid=?')
			->executeUncached($metaModel->get('id'))
			->fetchEach('id');

		if ($arrIds)
		{
			\Database::getInstance()
				->prepare(sprintf(
					'DELETE FROM tl_metamodel_rendersetting WHERE pid IN (%s)', implode(',', $arrIds))
				)
				->executeUncached();
		}

		// Delete the render settings.
		\Database::getInstance()
			->prepare('DELETE FROM tl_metamodel_rendersettings WHERE pid=?')
			->executeUncached($metaModel->get('id'));
	}

	/**
	 * Destroy the filter settings for a MetaModel.
	 *
	 * @param IMetaModel $metaModel The MetaModel to destroy.
	 *
	 * @return void
	 */
	protected static function destroyFilterSettings(IMetaModel $metaModel)
	{
		// Delete everything from filter settings.
		$arrIds = \Database::getInstance()
			->prepare('SELECT id FROM tl_metamodel_filter WHERE pid=?')
			->executeUncached($metaModel->get('id'))
			->fetchEach('id');
		if ($arrIds)
		{
			\Database::getInstance()
				->prepare(sprintf(
					'DELETE FROM tl_metamodel_filtersetting WHERE pid IN (%s)',
					implode(',', $arrIds))
				)
				->executeUncached();
		}
		\Database::getInstance()
			->prepare('DELETE FROM tl_metamodel_filter WHERE pid=?')
			->executeUncached($metaModel->get('id'));
	}

	/**
	 * Handle the deletion of a MetaModel and all attached data.
	 *
	 * @param PreDeleteModelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handle(PreDeleteModelEvent $event)
	{
		$metaModel = Factory::byId($event->getModel()->getId());
		if ($metaModel)
		{
			self::destroyAttributes($metaModel);
			self::destroyDcaCombinations($metaModel);
			self::destroyInputScreens($metaModel);
			self::destroyRenderSettings($metaModel);
			self::destroyFilterSettings($metaModel);

			TableManipulation::deleteTable($metaModel->getTableName());
		}
	}
}
