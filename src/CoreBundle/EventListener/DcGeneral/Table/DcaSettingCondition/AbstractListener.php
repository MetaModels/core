<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MetaModels\IFactory;
use MetaModels\IMetaModel;

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
    private RequestScopeDeterminator $scopeDeterminator;

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
    private Connection $connection;

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
     * @return IMetaModel
     *
     * @throws \RuntimeException Throws if you could not retrieve metamodel.
     * @throws Exception
     */
    public function getMetaModel(EnvironmentInterface $interface)
    {
        $inputProvider = $interface->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $metaModelId = $this->connection
            ->createQueryBuilder()
            ->select('d.pid')
            ->from('tl_metamodel_dca', 'd')
            ->leftJoin('d', 'tl_metamodel_dcasetting', 's', 'd.id=s.pid')
            ->where('s.id=:id')
            ->setParameter('id', ModelId::fromSerialized($inputProvider->getParameter('pid'))->getId())
            ->executeQuery();

        if (
            false !== ($metaModelId = $metaModelId->fetchOne())
            && ($tableName = $this->factory->translateIdToMetaModelName($metaModelId))
            && null !== ($metaModel = $this->factory->getMetaModel($tableName))
        ) {
            return $metaModel;
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

        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        if ('tl_metamodel_dcasetting_condition' !== $dataDefinition->getName()) {
            return false;
        }

        if ($event instanceof AbstractModelAwareEvent) {
            if ($dataDefinition->getName() !== $event->getModel()->getProviderName()) {
                return false;
            }
        }

        return true;
    }
}
