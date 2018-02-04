<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Dca;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;

/**
 * This provides the parent table options.
 */
class ParentTableOptionListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     * @param IFactory   $factory    The MetaModel factory.
     */
    public function __construct(Connection $connection, IFactory $factory)
    {
        $this->connection = $connection;
        $this->factory    = $factory;
    }

    /**
     * Retrieve a list of all backend sections, like "content", "system" etc.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (('tl_metamodel_dca' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('ptable' !== $event->getPropertyName())) {
            return;
        }

        $tables = [];
        foreach ($this->connection->getSchemaManager()->listTableNames() as $table) {
            $tables[$table] = $table;
        }

        if ('ctable' === $event->getModel()->getProperty('rendertype')) {
            $currentTable = $this->factory->translateIdToMetaModelName($event->getModel()->getProperty('pid'));
            $tables       = array_filter(
                $tables,
                function ($table) use ($currentTable) {
                    return ($currentTable !== $table);
                }
            );
        }

        $event->setOptions($tables);
    }
}
