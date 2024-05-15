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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSortGroup;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * This provides the attribute at generate.
 */
class SortGroupCreateListener
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * SortGroupCreateListener constructor.
     *
     * @param Connection $connection Database connection.
     */
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * Set as default if first item.
     *
     * @param PreEditModelEvent $event The event.
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle(PreEditModelEvent $event): void
    {
        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if ('tl_metamodel_dca_sortgroup' !== $dataDefinition->getName()) {
            return;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if ('paste' !== $inputProvider->getParameter('act') || !($pid = $inputProvider->getParameter('pid'))) {
            return;
        }

        if (!($pid = ModelId::fromSerialized($pid)->getId())) {
            return;
        }

        // Retrieve if first item.
        $statement = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_dca_sortgroup', 't')
            ->where('t.pid=:pid')
            ->setParameter('pid', $pid)
            ->executeQuery();

        if ($statement->rowCount()) {
            return;
        }

        // Set 'isdefault' as checked is first item.
        $model = $event->getModel();
        $model->setProperty('isdefault', 1);
    }
}
