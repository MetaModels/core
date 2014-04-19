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

namespace MetaModels\Events;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractContainerAwareEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;

/**
 * This event is triggered for every attribute when the data container is being built.
 */
class BuildAttributeEvent
	extends AbstractContainerAwareEvent
{
	/**
	 * The event name.
	 */
	const NAME = 'metamodels.build.attribute';

	/**
	 * The Attribute.
	 *
	 * @var IAttribute
	 */
	protected $attribute;

	/**
	 * The MetaModel instance.
	 *
	 * @var IMetaModel
	 */
	protected $metaModel;

	/**
	 * Create a new container aware event.
	 *
	 * @param IMetaModel         $metaModel     The MetaModel.
	 *
	 * @param IAttribute         $attribute     The attribute being built.
	 *
	 * @param ContainerInterface $dataContainer The data container information.
	 */
	public function __construct(IMetaModel $metaModel, IAttribute $attribute, ContainerInterface $dataContainer)
	{
		parent::__construct($dataContainer);

		$this->metaModel = $metaModel;
		$this->attribute = $attribute;
	}

	/**
	 * Retrieve the attribute.
	 *
	 * @return IAttribute
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * Retrieve the MetaModel.
	 *
	 * @return IMetaModel
	 */
	public function getMetaModel()
	{
		return $this->metaModel;
	}
}