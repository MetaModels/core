<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Events;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyVisibleCondition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class creates the default instances for property conditions when generating input screens.
 */
class DefaultPropertyConditionCreator
	implements EventSubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			CreatePropertyConditionEvent::NAME => __CLASS__ . '::handle'
		);
	}

	/**
	 * Create the property conditions.
	 *
	 * @param CreatePropertyConditionEvent $event The event.
	 *
	 * @return void
	 */
	public function handle(CreatePropertyConditionEvent $event)
	{
		$meta      = $event->getData();
		$metaModel = $event->getMetaModel();

		switch ($meta['type'])
		{
			case 'conditionor':
				$event->setInstance(new PropertyConditionChain(array(), ConditionChainInterface::OR_CONJUNCTION));
				break;
			case 'conditionand':
				$event->setInstance(new PropertyConditionChain(array(), ConditionChainInterface::AND_CONJUNCTION));
				break;
			case 'conditionpropertyvalueis':
				$event->setInstance(new PropertyValueCondition(
					$metaModel->getAttributeById($meta['attr_id'])->getColName(),
					$meta['value']
				));
				break;
			case 'conditionpropertyvisible':
				$event->setInstance(new PropertyVisibleCondition(
					$metaModel->getAttributeById($meta['attr_id'])->getColName())
				);
				break;
			default:
		}
	}
}
