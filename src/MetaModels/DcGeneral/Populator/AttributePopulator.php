<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Populator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\DcGeneral\Events\MetaModel\PopulateAttributeEvent;

/**
 * This class handles the MetaModels attribute populating.
 */
class AttributePopulator
{
    /**
     * The input screen to use.
     *
     * @var IInputScreen
     */
    private $inputScreen;

    /**
     * Create a new instance.
     *
     * @param IInputScreen $inputScreen The input screen in use.
     */
    public function __construct(IInputScreen $inputScreen)
    {
        $this->inputScreen = $inputScreen;
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
        $dispatcher = $environment->getEventDispatcher();
        $metaModel  = $this->inputScreen->getMetaModel();
        foreach ($metaModel->getAttributes() as $attribute) {
            $event = new PopulateAttributeEvent($metaModel, $attribute, $environment);
            // Trigger BuildAttribute Event.
            $dispatcher->dispatch($event::NAME, $event);
        }
    }
}
