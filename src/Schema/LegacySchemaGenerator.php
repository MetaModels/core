<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Schema;

use MetaModels\Attribute\IInternal;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\Information\MetaModelCollectionInterface;

use function in_array;

/**
 * This is the legacy handler for creating the legacy schema.
 *
 * @deprecated Only used as bc layer for 2.0 - to be removed in 3.0.
 */
class LegacySchemaGenerator implements SchemaGeneratorInterface
{
    /** @var list<string> */
    private array $ignoredTypeNames;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory     $factory The factory.
     * @param list<string> $ignoredTypeNames
     */
    public function __construct(IFactory $factory, array $ignoredTypeNames)
    {
        $this->factory = $factory;
        $this->ignoredTypeNames = $ignoredTypeNames;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SchemaInformation $information, MetaModelCollectionInterface $collection): void
    {
        /** @psalm-suppress DeprecatedClass */
        if (!$information->has(LegacySchemaInformation::class)) {
            /** @psalm-suppress DeprecatedClass */
            $information->add(new LegacySchemaInformation());
        }

        /**
         * @var LegacySchemaInformation $legacy
         * @psalm-suppress DeprecatedClass
         */
        $legacy = $information->get(LegacySchemaInformation::class);

        foreach ($collection as $metaModelInformation) {
            $metaModel = $this->factory->getMetaModel($metaModelInformation->getName());
            assert($metaModel instanceof IMetaModel);
            foreach ($metaModel->getAttributes() as $attribute) {
                // Skip managed and internal attributes.
                if (
                    $attribute instanceof IInternal
                    || \in_array($attribute->get('type'), $this->ignoredTypeNames, true)
                ) {
                    continue;
                }

                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Attribute type "' . $attribute->get('type') . '" should be changed to a managed attribute.',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd

                $legacy->addAttribute($attribute);
            }
        }
    }
}
