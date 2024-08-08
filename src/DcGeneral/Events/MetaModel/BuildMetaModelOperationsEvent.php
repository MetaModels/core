<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractContainerAwareEvent;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\InputScreen\InputScreen;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This event is triggered to allow adding of model operations for MetaModels when the data container is being built.
 */
class BuildMetaModelOperationsEvent extends AbstractContainerAwareEvent
{
    /**
     * The event name.
     */
    public const NAME = 'metamodels.dc-general.events.metamodel.build.metamodel.operations';

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The input screen in use.
     *
     * @var array
     */
    protected $inputScreen;

    /**
     * Create a new container aware event.
     *
     * @param IMetaModel         $metaModel     The MetaModel.
     * @param ContainerInterface $dataContainer The data container information.
     * @param array              $inputScreen   The input screen in use.
     */
    public function __construct(
        IMetaModel $metaModel,
        ContainerInterface $dataContainer,
        array $inputScreen
    ) {
        parent::__construct($dataContainer);

        $this->metaModel   = $metaModel;
        $this->inputScreen = $inputScreen;
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

    /**
     * Retrieve the input screen.
     *
     * @return array
     */
    public function getScreen()
    {
        return $this->inputScreen;
    }

    /**
     * Retrieve the input screen.
     *
     * @return IInputScreen
     *
     * @deprecated The InputScreen class will get removed.
     *
     * @psalm-suppress DeprecatedInterface
     * @psalm-suppress DeprecatedClass
     */
    public function getInputScreen()
    {
        $serviceContainer = System::getContainer()->get('cca.legacy_dic')->getService('metamodels-service-container');
        assert($serviceContainer instanceof IMetaModelsServiceContainer);

        return new InputScreen(
            $serviceContainer,
            $this->inputScreen['meta'],
            $this->inputScreen['properties'],
            $this->inputScreen['conditions'],
            $this->inputScreen['groupSort']
        );
    }
}
