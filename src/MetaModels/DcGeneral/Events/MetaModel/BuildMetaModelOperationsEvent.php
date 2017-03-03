<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractContainerAwareEvent;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\DcGeneral\Dca\Builder\Builder;
use MetaModels\IMetaModel;

/**
 * This event is triggered to allow adding of model operations for MetaModels when the data container is being built.
 */
class BuildMetaModelOperationsEvent extends AbstractContainerAwareEvent
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.dc-general.events.metamodel.build.metamodel.operations';

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The input screen in use.
     *
     * @var IInputScreen
     */
    protected $inputScreen;

    /**
     * Create a new container aware event.
     *
     * @param IMetaModel         $metaModel     The MetaModel.
     *
     * @param ContainerInterface $dataContainer The data container information.
     *
     * @param IInputScreen       $inputScreen   The input screen in use.
     */
    public function __construct(
        IMetaModel $metaModel,
        ContainerInterface $dataContainer,
        IInputScreen $inputScreen
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
     * @return IInputScreen
     */
    public function getInputScreen()
    {
        return $this->inputScreen;
    }
}
