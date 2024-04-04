<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\DcGeneral\Events\MetaModel\PopulateAttributeEvent;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class handles the MetaModels attribute populating.
 */
class AttributePopulator
{
    use MetaModelPopulatorTrait;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param IFactory                 $factory    The MetaModels factory.
     */
    public function __construct(EventDispatcherInterface $dispatcher, IFactory $factory)
    {
        $this->dispatcher = $dispatcher;
        $this->factory    = $factory;
    }

    /**
     * Populate the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    public function populate(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        $metaModel = $this->factory->getMetaModel($dataDefinition->getName());
        assert($metaModel instanceof IMetaModel);
        foreach ($metaModel->getAttributes() as $attribute) {
            $event = new PopulateAttributeEvent($metaModel, $attribute, $environment);
            // Trigger BuildAttribute Event.
            $this->dispatcher->dispatch($event, $event::NAME);
        }
    }
}
