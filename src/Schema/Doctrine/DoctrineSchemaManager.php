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

use MetaModels\Schema\SchemaInformation;
use MetaModels\Schema\SchemaManagerInterface;

/**
 * This is the base for a schema manager working with doctrine schemas.
 */
class DoctrineSchemaManager implements SchemaManagerInterface
{
    /**
     * The schema manipulator.
     *
     * @var DoctrineSchemaManipulator
     */
    private $manipulator;

    /**
     * Create a new instance.
     *
     * @param DoctrineSchemaManipulator $manipulator The database connection.
     */
    public function __construct(DoctrineSchemaManipulator $manipulator)
    {
        $this->manipulator = $manipulator;
    }

    /**
     * Pre-process the schema information.
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return void
     */
    public function preprocess(SchemaInformation $information): void
    {
        // If no information added, exit.
        if (!$information->has(DoctrineSchemaInformation::class)) {
            return;
        }
        /** @var DoctrineSchemaInformation $doctrine */
        $doctrine = $information->get(DoctrineSchemaInformation::class);

        foreach ($doctrine->getPreProcessors() as $preProcessor) {
            $preProcessor->process();
        }
    }

    /**
     * Pre-process the schema information.
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return void
     */
    public function process(SchemaInformation $information): void
    {
        // If no information added, exit.
        if (!$information->has(DoctrineSchemaInformation::class)) {
            return;
        }

        /** @var DoctrineSchemaInformation $doctrine */
        $doctrine = $information->get(DoctrineSchemaInformation::class);
        $this->manipulator->updateDatabase($doctrine);
    }

    /**
     * Post-process the schema information.
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return void
     */
    public function postprocess(SchemaInformation $information): void
    {
        // If no information added, exit.
        if (!$information->has(DoctrineSchemaInformation::class)) {
            return;
        }

        /** @var DoctrineSchemaInformation $doctrine */
        $doctrine = $information->get(DoctrineSchemaInformation::class);

        foreach ($doctrine->getPostProcessors() as $postProcessor) {
            $postProcessor->process();
        }
    }
}
