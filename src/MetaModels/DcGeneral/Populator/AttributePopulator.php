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

namespace MetaModels\DcGeneral\Populator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\DcGeneral\Events\MetaModel\PopulateAttributeEvent;
use MetaModels\Helper\ViewCombinations;
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
    private $dispatcher;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher       The event dispatcher.
     * @param ViewCombinations         $viewCombinations The view combinations.
     */
    public function __construct(EventDispatcherInterface $dispatcher, ViewCombinations $viewCombinations)
    {
        $this->dispatcher       = $dispatcher;
        $this->viewCombinations = $viewCombinations;
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
        $inputScreen = $this->viewCombinations->getInputScreenDetails($environment->getDataDefinition()->getName());
        $metaModel   = $inputScreen->getMetaModel();
        foreach ($metaModel->getAttributes() as $attribute) {
            $event = new PopulateAttributeEvent($metaModel, $attribute, $environment);
            // Trigger BuildAttribute Event.
            $this->dispatcher->dispatch($event::NAME, $event);
        }
    }
}
