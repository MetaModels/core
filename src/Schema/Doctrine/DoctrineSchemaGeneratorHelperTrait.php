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

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use MetaModels\Information\MetaModelInformationInterface;

/**
 * This trait provides common used table manipulation tasks.
 */
trait DoctrineSchemaGeneratorHelperTrait
{
    /**
     * Obtain the raw table schema for a MetaModel.
     *
     * @param DoctrineSchemaInformation     $schema               The schema to populate.
     * @param MetaModelInformationInterface $metaModelInformation The MetaModel information.
     *
     * @return Table
     */
    protected function getSchemaForMetaModel(
        DoctrineSchemaInformation $schema,
        MetaModelInformationInterface $metaModelInformation
    ): Table {
        $rawSchema = $schema->getSchema();
        if (!$rawSchema->hasTable($metaModelInformation->getName())) {
            return $rawSchema->createTable($metaModelInformation->getName());
        }

        return $rawSchema->getTable($metaModelInformation->getName());
    }

    /**
     * Update the information for a single column.
     *
     * @param Table  $tableSchema The table schema.
     * @param string $name        The table name.
     * @param string $typeName    The type name.
     * @param array  $options     The options.
     *
     * @return Column
     */
    protected function setColumnData(Table $tableSchema, string $name, string $typeName, array $options = []): Column
    {
        if (!$tableSchema->hasColumn($name)) {
            return $tableSchema->addColumn($name, $typeName, $options);
        }

        $column = $tableSchema->getColumn($name);
        $column->setType(Type::getType($typeName));
        if ([] !== $options) {
            $column->setOptions($options);
        }
        return $column;
    }
}
