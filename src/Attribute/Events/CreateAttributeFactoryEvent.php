<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute\Events;

use MetaModels\Attribute\IAttributeFactory;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered for every attribute factory instance that is created.
 */
class CreateAttributeFactoryEvent extends Event
{
    /**
     * The factory that has been created.
     *
     * @var IAttributeFactory
     */
    protected $factory;

    /**
     * Create a new instance.
     *
     * @param IAttributeFactory $factory The factory that has been created.
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve the attribute information array.
     *
     * @return IAttributeFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }
}
