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

namespace MetaModels\DcGeneral\Populator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;

/**
 * This trait delegates the populate handling to the populate() method if the environment belongs to a MetaModel.
 */
trait MetaModelPopulatorTrait
{
    /**
     * Handle the event.
     *
     * @param PopulateEnvironmentEvent $event The event.
     *
     * @return void
     */
    public function handle(PopulateEnvironmentEvent $event)
    {
        $environment = $event->getEnvironment();
        if ($environment->getDataDefinition() instanceof IMetaModelDataDefinition) {
            $this->populate($environment);
        }
    }

    /**
     * Populate the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    abstract protected function populate(EnvironmentInterface $environment);
}
