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

use DcGeneral\Data\ModelInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\IMetaModel;

class AttributeBase
{
	/**
	 * Retrieve the MetaModel the given model is attached to.
	 *
	 * @param ModelInterface $model The model being processed
	 *
	 * @return IMetaModel
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	protected static function getMetaModelFromModel(ModelInterface $model)
	{
		if (!(($model->getProviderName() == 'tl_metamodel_attribute') && $model->getId()))
		{
			throw new DcGeneralInvalidArgumentException(
				'Model must originate from tl_metamodel_attribute and be saved, this one originates from ' .
				$model->getProviderName() . ' and has id ' . $model->getId()
			);
		}

		return \MetaModels\Factory::byId($model->getProperty('pid'));
	}
}
