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

namespace MetaModels\DcGeneral\Events\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\Factory;
use MetaModels\IMetaModel;

/**
 * Base class for providing methods to retrieve various stuff related to a tl_metamodel_attribute model.
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class AttributeBase
{
	/**
	 * Retrieve the MetaModel the given model is attached to.
	 *
	 * @param ModelInterface $model The model being processed.
	 *
	 * @return IMetaModel
	 *
	 * @throws DcGeneralInvalidArgumentException When an invalid model has been passed.
	 */
	public static function getMetaModelFromModel(ModelInterface $model)
	{
		if (!(($model->getProviderName() == 'tl_metamodel_attribute') && $model->getProperty('pid')))
		{
			throw new DcGeneralInvalidArgumentException(
				'Model must originate from tl_metamodel_attribute and contain a pid, this one originates from ' .
				$model->getProviderName() . ' and has pid ' . $model->getProperty('pid')
			);
		}

		return Factory::byId($model->getProperty('pid'));
	}
}
