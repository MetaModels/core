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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is dispatched, whenever a MetaModels property condition shall be transformed into an object instance.
 */
class CreatePropertyConditionEvent extends Event
{
	const NAME = 'metamodels.events.create-property-condition-event';

	/**
	 * The array containing the meta information for the instance.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The MetaModel instance.
	 *
	 * @var IMetaModel
	 */
	protected $metaModel;

	/**
	 * The instance to be returned.
	 *
	 * @var PropertyConditionInterface
	 */
	protected $instance;

	/**
	 * Create a new instance.
	 *
	 * @param array      $data      The meta information for the instance.
	 *
	 * @param IMetaModel $metaModel The MetaModel instance the condition applies to.
	 */
	public function __construct($data, $metaModel)
	{
		$this->data      = $data;
		$this->metaModel = $metaModel;
	}

	/**
	 * Retrieve the meta data.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Retrieve the MetaModel instance.
	 *
	 * @return IMetaModel
	 */
	public function getMetaModel()
	{
		return $this->metaModel;
	}

	/**
	 * Retrieve the instance.
	 *
	 * @return PropertyConditionInterface
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * Set the instance.
	 *
	 * @param PropertyConditionInterface $instance The instance to be set.
	 *
	 * @return CreatePropertyConditionEvent
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;

		return $this;
	}
}
