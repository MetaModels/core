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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaCombine;

use Doctrine\DBAL\Connection;
use MultiColumnWizard\Event\GetOptionsEvent;

/**
 * This class handles obtaining the group options.
 */
class GroupOptionListener
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
     * @param Connection $connection The connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get all options for the user groups.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOptionsEvent $event)
    {
        if (('tl_metamodel_dca_combine' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('rows' !== $event->getPropertyName())
            || !in_array($event->getSubPropertyName(), ['be_group', 'fe_group'])) {
            return;
        }

        $isBackend = 'be_group' === $event->getSubPropertyName();

        $groups = $this
            ->connection
            ->createQueryBuilder()
            ->select('id')
            ->addSelect('name')
            ->from($isBackend ? 'tl_user_group' : 'tl_member_group')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result     = [];
        $result[-1] = $event->getEnvironment()->getTranslator()->translate(
            $isBackend ? 'sysadmin' : 'anonymous',
            'tl_metamodel_dca_combine'
        );

        foreach ($groups as $group) {
            $result[$group['id']] = $group['name'];
        }

        $event->setOptions($result);
    }
}
