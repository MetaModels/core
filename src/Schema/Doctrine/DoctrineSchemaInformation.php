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

use Doctrine\DBAL\Schema\Schema;
use MetaModels\Schema\SchemaInformationInterface;

/**
 * This is the encapsulation of doctrine schema information.
 */
class DoctrineSchemaInformation implements SchemaInformationInterface
{
    /**
     * The generated doctrine schema.
     *
     * @var Schema
     */
    private $schema;

    /**
     * A list of pre processors ordered by priority.
     *
     * @var SchemaProcessorInterface[][]
     */
    private $preProcessors = [];

    /**
     * A list of post processors by priority.
     *
     * @var SchemaProcessorInterface[][]
     */
    private $postProcessors = [];

    /**
     * Create a new instance.
     *
     * @param Schema|null $schema The contained doctrine schema.
     */
    public function __construct(Schema $schema = null)
    {
        $this->schema = ($schema ?? new Schema());
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Retrieve schema.
     *
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Add a pre processor.
     *
     * @param SchemaProcessorInterface $processor The processor to add.
     * @param int                      $priority  The priority to use.
     *
     * @return void
     */
    public function addPreProcessor(SchemaProcessorInterface $processor, $priority = 0): void
    {
        if (!isset($this->preProcessors[$priority])) {
            $this->preProcessors[$priority] = [];
            krsort($this->preProcessors);
        }
        $this->preProcessors[$priority][] = $processor;
    }

    /**
     * Retrieve preProcessors.
     *
     * @return SchemaProcessorInterface[]
     */
    public function getPreProcessors(): array
    {
        if ([] === $this->preProcessors) {
            return [];
        }

        return array_merge(...$this->preProcessors);
    }

    /**
     * Add a post processor.
     *
     * @param SchemaProcessorInterface $processor The processor to add.
     * @param int                      $priority  The priority to use.
     *
     * @return void
     */
    public function addPostProcessor(SchemaProcessorInterface $processor, $priority = 0): void
    {
        if (!isset($this->postProcessors[$priority])) {
            $this->postProcessors[$priority] = [];
            krsort($this->postProcessors);
        }
        $this->postProcessors[$priority][] = $processor;
    }

    /**
     * Retrieve postProcessors.
     *
     * @return SchemaProcessorInterface[]
     */
    public function getPostProcessors(): array
    {
        if ([] === $this->postProcessors) {
            return [];
        }

        return array_merge(...$this->postProcessors);
    }
}
