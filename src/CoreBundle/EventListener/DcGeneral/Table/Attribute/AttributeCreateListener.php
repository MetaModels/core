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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IFactory;

/**
 * This class takes care of updating all data when an attribute has been saved.
 */
class AttributeCreateListener extends BaseListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The connection.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IAttributeFactory $attributeFactory,
        IFactory $factory,
        Connection $connection
    ) {
        parent::__construct($scopeDeterminator, $attributeFactory, $factory);
        $this->connection = $connection;
    }

    /**
     * Handle the creation of an attribute - open same type as before.
     *
     * @param PreEditModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PreEditModelEvent $event): void
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        if ('create' !== $event->getEnvironment()->getInputProvider()->getParameter('act')
            || 'snc' !== $event->getEnvironment()->getInputProvider()->getParameter('btn')
            || !($after = $event->getEnvironment()->getInputProvider()->getParameter('after'))
        ) {
            return;
        }

        if (!($previousAttributeId = ModelId::fromSerialized($after)->getId())) {
            return;
        }

        // Retrieve the type of previous attribute.
        $statement = $this->connection->createQueryBuilder()
            ->select('t.type')
            ->from('tl_metamodel_attribute', 't')
            ->where('t.id=:id')
            ->setParameter('id', $previousAttributeId)
            ->setMaxResults(1)
            ->execute()
            ->fetchFirstColumn();

        if (empty($statement)) {
            return;
        }

        // Set type of previous attribute as default.
        $model = $event->getModel();
        $model->setProperty('type', $statement[0]);
    }
}
