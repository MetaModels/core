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

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\Factory;
use MetaModels\IMetaModel;

/**
 * Base class to be used in event listeners
 */
class InputScreenBase
{
	/**
	 * Retrieve the MetaModel the given model is attached to.
	 *
	 * @param ModelInterface $model The input screen model for which to retrieve the MetaModel.
	 *
	 * @return IMetaModel
	 *
	 * @throws DcGeneralInvalidArgumentException  When an invalid model has been passed or the model does not have an id.
	 */
	protected static function getMetaModelFromModel(ModelInterface $model)
	{
		if (!(($model->getProviderName() == 'tl_metamodel_dcasetting') && $model->getId()))
		{
			throw new DcGeneralInvalidArgumentException(
				sprintf(
					'Model must originate from tl_metamodel_dcasetting and be saved, this one originates from %s and has id %s',
					$model->getProviderName(),
					$model->getId()
				)
			);
		}

		$metaModelId = \Database::getInstance()
			->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
			->executeUncached($model->getProperty('pid'));

		return Factory::byId($metaModelId->pid);
	}
}
