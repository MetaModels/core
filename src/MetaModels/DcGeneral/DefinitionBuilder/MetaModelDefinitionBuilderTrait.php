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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;

/**
 * This trait delegates the building of the definition to the build method if it is a MetaModel container.
 */
trait MetaModelDefinitionBuilderTrait
{
    /**
     * Handle the event and delegate to the build method.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        $container = $event->getContainer();
        if ($container instanceof IMetaModelDataDefinition) {
            $this->build($container);
        }
    }

    /**
     * Build the definition.
     *
     * @param IMetaModelDataDefinition $container The container being built.
     *
     * @return void
     */
    abstract protected function build(IMetaModelDataDefinition $container);
}
