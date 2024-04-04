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

declare(strict_types=1);

namespace MetaModels\Schema;

/**
 * This is the main MetaModels schema manager, it is a composite of other managers.
 */
class SchemaManager implements SchemaManagerInterface
{
    /**
     * The list of registered schema managers.
     *
     * @var SchemaManagerInterface[]
     */
    private array $managers;

    /**
     * Create a new instance.
     *
     * @param SchemaManagerInterface[] $managers The managers to use.
     */
    public function __construct(array $managers)
    {
        $this->managers = $managers;
    }

    /**
     * {@inheritDoc}
     */
    public function preprocess(SchemaInformation $information): void
    {
        // pre process - this may perform data migrations and the like.
        foreach ($this->managers as $manager) {
            $manager->preprocess($information);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function process(SchemaInformation $information): void
    {
        // process - here the automatic adjustments to the db will be made.
        foreach ($this->managers as $manager) {
            $manager->process($information);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function postprocess(SchemaInformation $information): void
    {
        // post process - perform any cleanup to be done.
        foreach ($this->managers as $manager) {
            $manager->postprocess($information);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validate(SchemaInformation $information): array
    {
        $tasks = [];
        foreach ($this->managers as $manager) {
            $tasks[] = $manager->validate($information);
        }

        return array_merge(...$tasks);
    }
}
