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

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractContainerAwareEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;

/**
 * This event is triggered for every attribute when the data container is being built.
 */
class BuildAttributeEvent extends AbstractContainerAwareEvent
{
    /**
     * The event name.
     */
    public const NAME = 'metamodels.dc-general.events.metamodel.build.attribute';

    /**
     * The Attribute.
     *
     * @var IAttribute
     */
    protected $attribute;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * Create a new container aware event.
     *
     * @param IMetaModel         $metaModel     The MetaModel.
     *
     * @param IAttribute         $attribute     The attribute being built.
     *
     * @param ContainerInterface $dataContainer The data container information.
     */
    public function __construct(
        IMetaModel $metaModel,
        IAttribute $attribute,
        ContainerInterface $dataContainer
    ) {
        parent::__construct($dataContainer);

        $this->metaModel = $metaModel;
        $this->attribute = $attribute;
    }

    /**
     * Retrieve the attribute.
     *
     * @return IAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
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
}
