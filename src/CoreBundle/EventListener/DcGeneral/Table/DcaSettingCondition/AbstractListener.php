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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;

/**
 * This provides a way to obtain a MetaModel.
 */
abstract class AbstractListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection
    ) {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->factory           = $factory;
        $this->connection        = $connection;
    }

    /**
     * Retrieve the MetaModel attached to the model condition setting.
     *
     * @param EnvironmentInterface $interface The environment.
     *
     * @return \MetaModels\IMetaModel
     *
     * @throws \RuntimeException Throws if could not retrieve metamodel.
     */
    public function getMetaModel(EnvironmentInterface $interface)
    {
        $metaModelId = $this->connection->createQueryBuilder()
            ->select('d.pid')
            ->from('tl_metamodel_dca', 'd')
            ->leftJoin('d', 'tl_metamodel_dcasetting', 's', '(d.id=s.pid)')
            ->where('(s.id=:id)')
            ->setParameter('id', ModelId::fromSerialized($interface->getInputProvider()->getParameter('pid'))->getId())
            ->execute();

        if ($tableName = $this->factory->translateIdToMetaModelName($metaModelId = $metaModelId->fetchColumn())) {
            return $this->factory->getMetaModel($tableName);
        }

        throw new \RuntimeException('Could not retrieve MetaModel ' . $metaModelId);
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();
        if ('tl_metamodel_dcasetting_condition' !== $environment->getDataDefinition()->getName()) {
            return false;
        }

        if ($event instanceof AbstractModelAwareEvent) {
            if ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName()) {
                return false;
            }
        }

        return true;
    }
}
