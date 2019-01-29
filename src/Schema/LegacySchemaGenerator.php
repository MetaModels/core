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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\Schema;

use MetaModels\Attribute\IInternal;
use MetaModels\Attribute\ISchemaManagedAttribute;
use MetaModels\IFactory;
use MetaModels\Information\MetaModelCollectionInterface;

/**
 * This is the legacy handler for creating the legacy schema.
 *
 * @deprecated Only used as bc layer for 2.0 - to be removed in 3.0.
 */
class LegacySchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The factory.
     */
    public function __construct(IFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SchemaInformation $information, MetaModelCollectionInterface $collection): void
    {
        if (!$information->has(LegacySchemaInformation::class)) {
            $information->add(new LegacySchemaInformation());
        }

        /** @var LegacySchemaInformation $legacy */
        $legacy = $information->get(LegacySchemaInformation::class);

        foreach ($collection as $metaModelInformation) {
            $metaModel = $this->factory->getMetaModel($metaModelInformation->getName());
            foreach ($metaModel->getAttributes() as $attribute) {
                // Skip managed and internal attributes.
                if ($attribute instanceof ISchemaManagedAttribute || $attribute instanceof IInternal) {
                    continue;
                }

                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Attribute type "' . $attribute->get('type') .
                    '" should implement "' . ISchemaManagedAttribute::class . '".',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd

                $legacy->addAttribute($attribute);
            }
        }
    }
}
