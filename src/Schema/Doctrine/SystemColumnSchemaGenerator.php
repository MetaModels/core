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

use Doctrine\DBAL\Types\Types;
use MetaModels\Information\MetaModelCollectionInterface;
use MetaModels\Information\MetaModelInformationInterface;

/**
 * This interface describes a schema provider.
 */
class SystemColumnSchemaGenerator implements DoctrineSchemaGeneratorInterface
{
    use DoctrineSchemaGeneratorHelperTrait;

    /**
     * {@inheritDoc}
     */
    public function generate(DoctrineSchemaInformation $schema, MetaModelCollectionInterface $collection): void
    {
        foreach ($collection as $metaModelInformation) {
            $this->generateMetaModelSchema($schema, $metaModelInformation);
        }
    }

    /**
     * Generate the schema for a MetaModel.
     *
     * @param DoctrineSchemaInformation     $schema               The doctrine schema to populate.
     * @param MetaModelInformationInterface $metaModelInformation The metamodel information to use.
     */
    private function generateMetaModelSchema(
        DoctrineSchemaInformation $schema,
        MetaModelInformationInterface $metaModelInformation
    ): void {
        $tableSchema = $this->getSchemaForMetaModel($schema, $metaModelInformation);

        $this->setColumnData($tableSchema, 'id', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => true,
            'autoincrement' => true,
        ]);
        $this->setColumnData($tableSchema, 'pid', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => true,
            'default' => 0,
        ]);
        $this->setColumnData($tableSchema, 'sorting', Types::INTEGER, [
            'unsigned' => true,
            'default' => 0,
        ]);
        $this->setColumnData($tableSchema, 'tstamp', Types::INTEGER, [
            'unsigned' => true,
            'default' => 0,
        ]);

        $tableSchema->setPrimaryKey(['id']);

        if ($metaModelInformation->hasConfigurationValue('varsupport')
            && '1' === $metaModelInformation->getConfigurationValue('varsupport')) {
            $this->setColumnData($tableSchema, 'varbase', Types::STRING, [
                'length' => 1,
                'fixed' => true,
                'default' => '',
            ]);
            $this->setColumnData($tableSchema, 'vargroup', Types::INTEGER, [
                'unsigned' => true,
                'default' => 0,
            ]);
        }
    }
}
