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

declare(strict_types = 1);

namespace MetaModels\InformationProvider;

use Doctrine\DBAL\Connection;
use MetaModels\Information\AttributeInformation;
use MetaModels\Information\MetaModelInformation;

/**
 * This adds information from the tl_metamodel_* tables in Contao.
 */
class ContaoDatabaseBackedInformationProvider implements InformationProviderInterface
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getNames(): array
    {
        if ($this->connection->createSchemaManager()->tablesExist('tl_metamodel')) {
            return $this
                ->connection
                ->createQueryBuilder()
                ->select('tableName')
                ->from('tl_metamodel')
                ->executeQuery()
                ->fetchAllAssociative();
        }

        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException On error.
     */
    public function getInformationFor(MetaModelInformation $information): void
    {
        $configuration = $this
            ->connection
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel')
            ->where('tableName=:tableName')
            ->setParameter('tableName', $information->getName())
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAllAssociative();

        // Not managed by us.
        if (empty($configuration)) {
            return;
        }

        $information->addConfiguration($configuration);

        // Now add the attributes.
        $attributeRows = $this
            ->connection
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_attribute')
            ->where('pid=:pid')
            ->setParameter('pid', $information->getConfigurationValue('id'))
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($attributeRows as $attributeRow) {
            $colName = $attributeRow['colname'];
            if (!$information->hasAttribute($colName)) {
                $information->addAttribute(new AttributeInformation($colName, $attributeRow['type'], $attributeRow));
                continue;
            }

            $attribute = $information->getAttribute($colName);
            $attribute->addConfiguration($attributeRow);
        }
    }
}
