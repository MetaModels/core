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

namespace MetaModels\Events;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\IMetaModel;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched, whenever a MetaModels property condition shall be transformed into an object instance.
 *
 * @deprecated Implement proper factories and create conditions within there.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CreatePropertyConditionEvent extends Event
{
    public const NAME = 'metamodels.events.create-property-condition-event';

    /**
     * The array containing the meta information for the instance.
     *
     * @var array
     */
    protected $data;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The instance to be returned.
     *
     * @var PropertyConditionInterface|null
     */
    protected $instance = null;

    /**
     * Create a new instance.
     *
     * @param array      $data      The meta information for the instance.
     * @param IMetaModel $metaModel The MetaModel instance the condition applies to.
     */
    public function __construct($data, IMetaModel $metaModel)
    {
        $this->data      = $data;
        $this->metaModel = $metaModel;
    }

    /**
     * Retrieve the meta data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
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
     * Retrieve the instance.
     *
     * @return PropertyConditionInterface|null
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Set the instance.
     *
     * @param PropertyConditionInterface $instance The instance to be set.
     *
     * @return CreatePropertyConditionEvent
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }
}
