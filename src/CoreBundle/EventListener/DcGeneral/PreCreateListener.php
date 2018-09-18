<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use MetaModels\DcGeneral\DataDefinition\MetaModelDataDefinition;
use MetaModels\IFactory;

/**
 * This class changes the definition container type.
 */
class PreCreateListener
{
    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The MetaModels factory.
     */
    public function __construct(IFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle the event.
     *
     * @param PreCreateDcGeneralEvent $event The event.
     *
     * @return void
     */
    public function handle(PreCreateDcGeneralEvent $event)
    {
        $factory = $event->getFactory();
        if (!in_array($factory->getContainerName(), $this->factory->collectNames())) {
            return;
        }

        $factory->setContainerClassName(MetaModelDataDefinition::class);
    }
}
