<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use MetaModels\IFactory;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered to collect the names of all known MetaModels.
 */
class CollectMetaModelTableNamesEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.metamodel.collect-table-names';

    /**
     * The factory calling the event.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The names of the MetaModels.
     *
     * @var string[]
     */
    protected $metaModelNames = array();

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The MetaModel factory dispatching this event.
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve the factory.
     *
     * @return IFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Retrieve the MetaModel name to be created.
     *
     * @return string[]
     */
    public function getMetaModelNames()
    {
        return $this->metaModelNames;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param string[] $metaModelNames The MetaModel names.
     *
     * @return CollectMetaModelTableNamesEvent
     */
    public function setMetaModelNames($metaModelNames)
    {
        $this->metaModelNames = $metaModelNames;

        return $this;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param string[] $metaModelNames The MetaModel names.
     *
     * @return CollectMetaModelTableNamesEvent
     */
    public function addMetaModelNames($metaModelNames)
    {
        $this->metaModelNames = array_unique(array_merge($this->metaModelNames, $metaModelNames));

        return $this;
    }
}
