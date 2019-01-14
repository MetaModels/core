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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\AbstractWeightAwarePaletteCondition;
use Doctrine\DBAL\Connection;

/**
 * Condition for the default palette.
 */
class RenderSettingAttributeIs extends AbstractWeightAwarePaletteCondition
{
    /**
     * The expected property value.
     *
     * @var mixed
     */
    protected $attributeType;

    /**
     * Buffer the attribute types to ease lookup.
     *
     * @var array
     */
    protected static $attributeTypes = array();

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param string          $attributeType The attribute type name.
     *
     * @param int             $weight        The weight of this condition to apply.
     *
     * @param Connection|null $connection    Database connection.
     */
    public function __construct($attributeType, $weight = 1, Connection $connection = null)
    {
        $this->attributeType = $attributeType;
        $this->setWeight($weight);

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
        }

        $this->connection = $connection;
    }

    /**
     * Set the attribute type name.
     *
     * @param string $attributeType The attribute type name.
     *
     * @return RenderSettingAttributeIs
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * Retrieve the attribute type name.
     *
     * @return mixed
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
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException When an database error occurs.
     */
    public function getTypeOfAttribute($value)
    {
        if (!isset(self::$attributeTypes[$value])) {
            $statement = $this->connection->prepare('SELECT type FROM tl_metamodel_attribute WHERE id=? LIMIT 0,1');
            $statement->bindValue(1, $value);
            $statement->execute();

            self::$attributeTypes[$value] = $statement->fetch(\PDO::FETCH_OBJ)->type;
        }

        return self::$attributeTypes[$value];
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
    {
        if ($input && $input->hasPropertyValue('attr_id')) {
            $value = $input->getPropertyValue('attr_id');
        } elseif ($model) {
            $value = $model->getProperty('attr_id');
        } else {
            return false;
        }

        return ($this->getTypeOfAttribute($value) == $this->getAttributeType()) ? $this->getWeight() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }
}
