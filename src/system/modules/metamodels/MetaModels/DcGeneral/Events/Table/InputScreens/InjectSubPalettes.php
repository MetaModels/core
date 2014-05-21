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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Build the filter for sub palette conditions.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreens
 */
class InjectSubPalettes
{
	/**
	 * Inject the sub palette filter if the current backend call is containing a sub palette.
	 *
	 * @param PopulateEnvironmentEvent $event The event.
	 *
	 * @return void
	 */
	public static function build(PopulateEnvironmentEvent $event)
	{
		$environment = $event->getEnvironment();
		$input       = $environment->getInputProvider();
		$conditions  = $environment->getDataDefinition()->getModelRelationshipDefinition();
		$condition   = $conditions->getChildCondition('tl_metamodel_dca', 'tl_metamodel_dcasetting');

		if (!$condition)
		{
			$condition = new ParentChildCondition();
			$condition
				->setSourceName('tl_metamodel_dca')
				->setDestinationName('tl_metamodel_dcasetting');
			$conditions->addChildCondition($condition);
		}

		if ($input->hasParameter('subpaletteid'))
		{
			$subPalette = IdSerializer::fromSerialized($input->getParameter('subpaletteid'))->getId();
			$builder    = new FilterBuilder($condition->getFilterArray());
			$condition->setFilterArray($builder
				->getFilter()
				->andRemotePropertyEquals('subpalette', $subPalette, true)
				->getAllAsArray()
			);
			$setters   = (array)$condition->getSetters();
			$setters[] = array
			(
				'to_field' => 'subpalette',
				'value'    => $subPalette
			);
			$condition->setSetters($setters);
		}
		else
		{
			$builder = new FilterBuilder($condition->getFilterArray());
			$condition->setFilterArray($builder
					->getFilter()
					->andRemotePropertyEquals('subpalette', 0, true)
					->getAllAsArray()
			);
		}
	}
}
