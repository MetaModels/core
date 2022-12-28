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

use Doctrine\DBAL\Schema\Table;
use MetaModels\Information\AttributeInformation;
use MetaModels\Information\MetaModelCollectionInterface;

/**
 * This abstract class eases schema generators for single types.
 */
abstract class AbstractAttributeTypeSchemaGenerator implements DoctrineSchemaGeneratorInterface
{
    use DoctrineSchemaGeneratorHelperTrait;

    /**
     * {@inheritDoc}
     */
    public function generate(DoctrineSchemaInformation $schema, MetaModelCollectionInterface $collection): void
    {
        $typeName = $this->getTypeName();
        foreach ($collection as $metaModelInformation) {
            if ([] !== $attributes = $metaModelInformation->getAttributesOfType($typeName)) {
                $tableSchema = $this->getSchemaForMetaModel($schema, $metaModelInformation);

                foreach ($attributes as $attribute) {
                    $this->generateAttribute($tableSchema, $attribute);
                }
            }
        }
    }

    /**
     * Generate an attribute.
     *
     * @param Table                $tableSchema The table schema.
     * @param AttributeInformation $attribute   The attribute to generate.
     *
     * @return void
     */
    abstract protected function generateAttribute(Table $tableSchema, AttributeInformation $attribute): void;

    /**
     * Obtain the type name.
     *
     * @return string
     */
    abstract protected function getTypeName(): string;
}
