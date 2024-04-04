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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use Doctrine\DBAL\Connection;

/**
 * Matches the attribute with the id in the named column against the passed type.
 */
class AttributeByIdIsOfType implements PropertyConditionInterface
{
    /**
     * The expected property value.
     *
     * @var mixed
     */
    private $attributeType;

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Buffer the attribute types to ease lookup.
     *
     * @var array
     */
    private static $attributeTypes = [];

    /**
     * The field in the models where the id is stored.
     *
     * @var string
     */
    private $idProperty;

    /**
     * Create a new instance.
     *
     * @param string     $attributeType The attribute type name.
     * @param Connection $connection    Database connection.
     * @param string     $idProperty    The field in the models where the id is stored.
     */
    public function __construct($attributeType, Connection $connection, $idProperty)
    {
        $this->attributeType = $attributeType;
        $this->connection    = $connection;
        $this->idProperty    = $idProperty;
    }

    /**
     * Set the attribute type name.
     *
     * @param string $attributeType The attribute type name.
     *
     * @return self
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * Retrieve the attribute type name.
     *
     * @return string
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * Retrieve the type name from an attribute.
     *
     * @param int $value The id of an attribute.
     *
     * @return ?string Return the id or null if not found.
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTypeOfAttribute($value)
    {
        if (!isset(self::$attributeTypes[$value])) {
            $statement = $this
                ->connection
                ->createQueryBuilder()
                ->select('t.type')
                ->from('tl_metamodel_attribute', 't')
                ->where('t.id=:id')
                ->setParameter('id', $value)
                ->setMaxResults(1)
                ->executeQuery();

            $result = $statement->fetchFirstColumn();
            if (count($result) > 0) {
                self::$attributeTypes[$value] = \current($result);
            } else {
                self::$attributeTypes[$value] = null;
            }
        }

        return self::$attributeTypes[$value];
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if ($input && $input->hasPropertyValue($this->idProperty)) {
            $value = $input->getPropertyValue($this->idProperty);
        } elseif ($model) {
            $value = $model->getProperty($this->idProperty);
        } else {
            return false;
        }

        return $this->getTypeOfAttribute($value) === $this->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
