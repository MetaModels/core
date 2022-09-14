<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use LogicException;
use MetaModels\InformationProvider\MetaModelInformationCollector;
use MetaModels\Schema\Doctrine\DoctrineSchemaInformation;
use MetaModels\Schema\SchemaGenerator;
use MetaModels\Schema\SchemaInformation;

final class DoctrineSchemaListener
{
    private SchemaGenerator $generator;

    private MetaModelInformationCollector $collector;

    public function __construct(
        SchemaGenerator $generator,
        MetaModelInformationCollector $collector
    ) {
        $this->generator = $generator;
        $this->collector = $collector;
    }

    /**
     * Adds the MetaModels database schema information to the Doctrine schema.
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        $this->generator->generate($information = new SchemaInformation(), $this->collector->getCollection());

        $contaoSchema = $event->getSchema();
        if ($schema = $information->get(DoctrineSchemaInformation::class)) {
            if (!$schema instanceof DoctrineSchemaInformation) {
                throw new LogicException('Invalid schema information obtained.');
            }
            $this->mergeSchema($schema, $contaoSchema);
        }
    }

    private function mergeSchema(DoctrineSchemaInformation $source, Schema $target): void
    {
        foreach ($source->getSchema()->getTables() as $sourceTable) {
            $tableName = $sourceTable->getName();
            if (!$target->hasTable($tableName)) {
                $target->createTable($tableName);
            }
            $targetTable = $target->getTable($tableName);
            $this->mergeTable($sourceTable, $targetTable);
        }
    }

    private function mergeTable(Table $sourceTable, Table $targetTable): void
    {
        $this->mergeColumns($sourceTable, $targetTable);
        $this->mergeIndexes($sourceTable, $targetTable);
        $this->mergeUniqueConstraints($sourceTable, $targetTable);
        $this->mergeForeignKeyConstraints($sourceTable, $targetTable);
        foreach ($sourceTable->getOptions() as $optionName => $optionValue) {
            $targetTable->addOption($optionName, $optionValue);
        }

        // NOTE: We are explicitely not copying the property: SchemaConfig|null Table::$_schemaConfig
    }

    private function mergeColumns(Table $sourceTable, Table $targetTable): void
    {
        foreach ($sourceTable->getColumns() as $sourceColumn) {
            $name                = $sourceColumn->getName();
            $options = [
                'default'             => $sourceColumn->getDefault(),
                'notnull'             => $sourceColumn->getNotnull(),
                'length'              => $sourceColumn->getLength(),
                'precision'           => $sourceColumn->getPrecision(),
                'scale'               => $sourceColumn->getScale(),
                'fixed'               => $sourceColumn->getFixed(),
                'unsigned'            => $sourceColumn->getUnsigned(),
                'autoincrement'       => $sourceColumn->getAutoincrement(),
                'columnDefinition'    => $sourceColumn->getColumnDefinition(),
                'comment'             => $sourceColumn->getComment(),
            ];
            $platformOptions     = $sourceColumn->getPlatformOptions();
            $customSchemaOptions = $sourceColumn->getCustomSchemaOptions();

            if ($targetTable->hasColumn($name)) {
                $tmpColumn = $targetTable->getColumn($name);
                $platformOptions     = array_merge($tmpColumn->getPlatformOptions(), $platformOptions);
                $customSchemaOptions = array_merge($tmpColumn->getCustomSchemaOptions(), $customSchemaOptions);
            }

            $targetTable
                ->addColumn($name, $sourceColumn->getType()->getName(), $options)
                ->setPlatformOptions($platformOptions)
                ->setCustomSchemaOptions($customSchemaOptions);

        }
    }

    private function mergeIndexes(Table $sourceTable, Table $targetTable): void
    {
        foreach ($sourceTable->getIndexes() as $source) {
            $name    = $source->getName();
            $columns = $source->getColumns();
            $flags   = $source->getFlags();
            $options = $source->getOptions();
            $unique  = $source->isUnique();
            $primary = $source->isPrimary();
            if ($targetTable->hasIndex($name)) {
                $tmpIndex = $targetTable->getIndex($name);
                $targetTable->dropIndex($name);
                $columns = array_merge($tmpIndex->getColumns(), $columns);
                $flags = array_merge($tmpIndex->getFlags(), $flags);
                $options = array_merge($tmpIndex->getOptions(), $options);
                $unique  = $unique || $source->isUnique();
                $primary = $primary || $source->isPrimary();
            }
            if ($primary) {
                $targetTable->setPrimaryKey($columns, $name);
                continue;
            }
            if ($unique) {
                $targetTable->addUniqueIndex($columns, $name, $options);
                continue;
            }
            $targetTable->addIndex($columns, $name, $flags, $options);
        }
    }

    private function mergeUniqueConstraints(Table $sourceTable, Table $targetTable): void
    {
        foreach ($sourceTable->getUniqueConstraints() as $uniqueConstraint) {
            $name = $uniqueConstraint->getName();
            $columns = $uniqueConstraint->getColumns();
            $flags = $uniqueConstraint->getFlags();
            $options = $uniqueConstraint->getOptions();
            if ($targetTable->hasUniqueConstraint($name)) {
                $tmpUniqueConstraint = $targetTable->getUniqueConstraint($name);
                $targetTable->removeUniqueConstraint($name);
                $columns = array_merge($tmpUniqueConstraint->getColumns(), $columns);
                $flags = array_merge($tmpUniqueConstraint->getFlags(), $flags);
                $options = array_merge($tmpUniqueConstraint->getOptions(), $options);
            }

            $targetTable->addUniqueConstraint($columns, $name, $flags, $options);
        }
    }

    private function mergeForeignKeyConstraints(Table $sourceTable, Table $targetTable): void
    {
        foreach ($sourceTable->getForeignKeys() as $foreignKey) {
            $name = $foreignKey->getName();
            $foreignTable = $foreignKey->getForeignTableName();
            $localColumns = $foreignKey->getLocalColumns();
            $foreignColumns = $foreignKey->getForeignColumns();
            $options = $foreignKey->getOptions();
            if ($targetTable->hasForeignKey($name)) {
                $tmpUniqueConstraint = $targetTable->getForeignKey($name);
                $targetTable->removeUniqueConstraint($name);
                $localColumns = array_merge($tmpUniqueConstraint->getLocalColumns(), $localColumns);
                $foreignColumns = array_merge($tmpUniqueConstraint->getForeignColumns(), $foreignColumns);
                $options = array_merge($tmpUniqueConstraint->getOptions(), $options);
            }
            $targetTable->addForeignKeyConstraint($foreignTable, $localColumns, $foreignColumns, $options, $name);
        }
    }
}
