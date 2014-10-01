<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\Events;

use MetaModels\Attribute\IFactory;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for every attribute factory instance that is created.
 */
class CreateAttributeFactoryEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.attribute.factory.create';

    /**
     * The factory that has been created.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The factory that has been created.
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve the attribute information array.
     *
     * @return IFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }
}
