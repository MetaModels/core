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

namespace MetaModels\Schema\Doctrine;

use MetaModels\Information\MetaModelCollectionInterface;

/**
 * This interface describes a schema provider.
 */
interface DoctrineSchemaGeneratorInterface
{
    /**
     * Generate a schema.
     *
     * @param DoctrineSchemaInformation    $schema     The doctrine schema to populate.
     * @param MetaModelCollectionInterface $collection The collection of MetaModels.
     *
     * @return void
     */
    public function generate(DoctrineSchemaInformation $schema, MetaModelCollectionInterface $collection): void;
}
