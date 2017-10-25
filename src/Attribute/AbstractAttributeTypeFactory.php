<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute;

use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Helper\TableManipulator;

/**
 * This is an abstract factory to query instances of attributes.
 *
 * Extend your own attribute factories from this class and register them when the create attribute factory event is
 * triggered.
 */
abstract class AbstractAttributeTypeFactory implements IAttributeTypeFactory
{
    /**
     * The type name.
     *
     * @var string
     */
    protected $typeName;

    /**
     * The name of the attribute class of this type.
     *
     * @var string
     */
    protected $typeClass;

    /**
     * The icon representing this attributy type.
     *
     * @var string
     */
    protected $typeIcon;

    /**
     * Database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Table manipulator.
     *
     * @var TableManipulator
     */
    protected $tableManipulator;

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeIcon()
    {
        return $this->typeIcon;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information, $this->connection, $this->tableManipulator);
    }

    /**
     * Create a new instance.
     *
     * @param Connection|null       $connection       Database connection.
     * @param TableManipulator|null $tableManipulator Table manipulator.
     */
    protected function __construct(Connection $connection = null, TableManipulator $tableManipulator = null)
    {
        if (null === $connection) {
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            $connection = System::getContainer()->get('database_connection');
        }

        if (null === $tableManipulator) {
            @trigger_error(
                'Table manipulator is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );

            $tableManipulator = System::getContainer()->get('metamodels.table_manipulator');
        }

        $this->connection       = $connection;
        $this->tableManipulator = $tableManipulator;
    }

    /**
     * Check if the type is translated.
     *
     * @return bool
     */
    public function isTranslatedType()
    {
        return in_array('MetaModels\Attribute\ITranslated', class_implements($this->typeClass, true));
    }

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType()
    {
        return in_array('MetaModels\Attribute\ISimple', class_implements($this->typeClass, true));
    }

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType()
    {
        return in_array('MetaModels\Attribute\IComplex', class_implements($this->typeClass, true));
    }
}
