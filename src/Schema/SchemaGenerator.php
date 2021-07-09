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

use MetaModels\Information\MetaModelCollectionInterface;

/**
 * This is the main MetaModels schema generator, it is a composite of other generators.
 */
class SchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * The list of registered schema managers.
     *
     * @var SchemaGeneratorInterface[]
     */
    private $generators;

    /**
     * Create a new instance.
     *
     * @param SchemaGeneratorInterface[] $generators The managers to use.
     */
    public function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SchemaInformation $information, MetaModelCollectionInterface $collection): void
    {
        foreach ($this->generators as $manager) {
            $manager->generate($information, $collection);
        }
    }
}
