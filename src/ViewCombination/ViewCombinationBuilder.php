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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ViewCombination;

use Doctrine\DBAL\Connection;
use MetaModels\IFactory;

/**
 * This builds the view combinations for an user.
 */
class ViewCombinationBuilder
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     * @param IFactory   $factory    The MetaModels factory.
     */
    public function __construct(Connection $connection, IFactory $factory)
    {
        $this->connection = $connection;
        $this->factory    = $factory;
    }

    /**
     * Retrieve the combinations for the passed user.
     *
     * @param string[] $userGroups The user groups.
     * @param string   $userType   The user type ('fe' or 'be').
     *
     * @return array|null
     *
     * @throws \InvalidArgumentException When the user type is unknown.
     */
    public function getCombinationsForUser($userGroups, $userType)
    {
        $userType = strtolower($userType);
        if ('fe' !== $userType && 'be' !== $userType) {
            throw new \InvalidArgumentException('Unknown user type: ' . $userType);
        }

        return $this->getCombinationsFromDatabase($userGroups, $userType);
    }

    /**
     * Retrieve the palette combinations from the database.
     *
     * @param string $userGroups The user groups of the user to fetch information for.
     * @param string $userType   The user type.
     *
     * @return null|array
     */
    private function getCombinationsFromDatabase($userGroups, $userType)
    {
        if (empty($userGroups)) {
            return null;
        }

        $builder = $this
            ->connection
            ->createQueryBuilder();

        $combinations = $builder
            ->select('*')
            ->from('tl_metamodel_dca_combine', 't')
            ->where($builder->expr()->in('t.' . $userType . '_group', ':groupList'))
            ->setParameter('groupList', $userGroups, Connection::PARAM_STR_ARRAY)
            ->orWhere('t.' . $userType . '_group=0')
            ->orderBy('t.pid')
            ->addOrderBy('t.sorting')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [
            'byName' => [],
            'byId' => []
        ];

        foreach ($combinations as $combination) {
            $metaModelId = $combination['pid'];
            if (isset($result['byId'][$metaModelId])) {
                continue;
            }
            $name = $this->factory->translateIdToMetaModelName($metaModelId);

            $result['byId'][$metaModelId] = $result['byName'][$name] = [
                'dca_id'   => $combination['dca_id'],
                'view_id' => $combination['view_id']
            ];
        }

        return $result;
    }
}
