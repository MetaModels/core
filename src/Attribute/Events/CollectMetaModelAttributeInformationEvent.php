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

use MetaModels\IMetaModel;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered for every metamodel when the attribute information for the MetaModel shall be retrieved.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class CollectMetaModelAttributeInformationEvent extends Event
{
    /**
     * The event name.
     */
    public const NAME = 'metamodels.metamodel.collect-metamodel-attribute-information';

    /**
     * The MetaModel instance being created.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The attribute information.
     *
     * @var array[]
     */
    protected $attributeInformation = array();

    /**
     * Create a new instance.
     *
     * @param IMetaModel $metaModel The name of the MetaModel to collect the attribute information for.
     */
    public function __construct(IMetaModel $metaModel)
    {
        $this->metaModel = $metaModel;
    }

    /**
     * Retrieve the MetaModel instance.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Retrieve the attribute information.
     *
     * @return array
     */
    public function getAttributeInformation()
    {
        return $this->attributeInformation;
    }

    /**
     * Set the attribute information.
     *
     * @param array[] $attributeInformation The attribute information.
     *
     * @return CollectMetaModelAttributeInformationEvent
     */
    public function setAttributeInformation($attributeInformation)
    {
        $this->attributeInformation = $attributeInformation;

        return $this;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param string $attributeName        The internal name of the attribute (column name).
     *
     * @param array  $attributeInformation The attribute information.
     *
     * @return CollectMetaModelAttributeInformationEvent
     */
    public function addAttributeInformation($attributeName, $attributeInformation)
    {
        $this->attributeInformation[$attributeName] = $attributeInformation;

        return $this;
    }
}
