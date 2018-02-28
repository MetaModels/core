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

namespace MetaModels\Attribute\Events;

use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for every attribute when the factory wants to create an instance.
 */
class CreateAttributeEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.attribute.create';

    /**
     * The attribute information.
     *
     * @var array
     */
    protected $attributeInformation;

    /**
     * The MetaModel instance this attribute shall be created for.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The attribute instance.
     *
     * @var IAttribute
     */
    protected $attribute;

    /**
     * Create a new instance.
     *
     * @param array      $attributeInformation The attribute information array.
     *
     * @param IMetaModel $metaModel            The MetaModel instance for which the attribute shall get created for.
     */
    public function __construct($attributeInformation, $metaModel)
    {
        $this->attributeInformation = $attributeInformation;
        $this->metaModel            = $metaModel;
    }

    /**
     * Retrieve the attribute information array.
     *
     * @return array
     */
    public function getAttributeInformation()
    {
        return $this->attributeInformation;
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
     * Retrieve the attribute instance.
     *
     * @return IAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set the attribute instance.
     *
     * @param IAttribute $attribute The instance to store.
     *
     * @return CreateAttributeEvent
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }
}
