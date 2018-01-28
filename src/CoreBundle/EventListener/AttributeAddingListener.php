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

namespace MetaModels\CoreBundle\EventListener;

use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Events\CreateMetaModelEvent;

/**
 * This listener adds the MetaModels attributes to a created MetaModel instance.
 */
class AttributeAddingListener
{
    /**
     * The attribute factory.
     *
     * @var IAttributeFactory
     */
    private $attributeFactory;

    /**
     * Create a new instance.
     *
     * @param IAttributeFactory $attributeFactory
     */
    public function __construct(IAttributeFactory $attributeFactory)
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Add attributes to the MetaModel.
     *
     * @param CreateMetaModelEvent $event The event.
     *
     * @return void
     */
    public function handle(CreateMetaModelEvent $event)
    {
        if (null === $metaModel = $event->getMetaModel()) {
            return;
        }

        foreach ($this->attributeFactory->createAttributesForMetaModel($metaModel) as $attribute) {
            $metaModel->addAttribute($attribute);
        }
    }
}
