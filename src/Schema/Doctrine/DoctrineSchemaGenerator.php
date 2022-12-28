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

namespace MetaModels\Schema\Doctrine;

use MetaModels\Information\MetaModelCollectionInterface;
use MetaModels\Schema\SchemaGeneratorInterface;
use MetaModels\Schema\SchemaInformation;

/**
 * This is the base for a schema generator working with doctrine schemas.
 */
class DoctrineSchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * The list of schema providers.
     *
     * @var DoctrineSchemaGeneratorInterface[]
     */
    private $providers;

    /**
     * Create a new instance.
     *
     * @param array $providers The schema providers.
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SchemaInformation $information, MetaModelCollectionInterface $collection): void
    {
        if (!$information->has(DoctrineSchemaInformation::class)) {
            $information->add(new DoctrineSchemaInformation());
        }
        /** @var DoctrineSchemaInformation $schema */
        $schema = $information->get(DoctrineSchemaInformation::class);

        foreach ($this->providers as $provider) {
            $provider->generate($schema, $collection);
        }
    }
}
