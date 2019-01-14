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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use Doctrine\DBAL\Connection;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\IFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class handles the populating of the Environments.
 */
class DataProviderPopulator
{
    use MetaModelPopulatorTrait;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The event dispatcher to pass to drivers.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher = null;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param IFactory                 $factory    The MetaModel factory.
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param Connection               $connection The database connection.
     */
    public function __construct(IFactory $factory, EventDispatcherInterface $dispatcher, Connection $connection)
    {
        $this->factory    = $factory;
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
    }

    /**
     * Populate the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    public function populate(EnvironmentInterface $environment)
    {
        foreach ([
            $environment->getDataDefinition(),
            $environment->getParentDataDefinition(),
            $environment->getRootDataDefinition()
        ] as $definition) {
            if (!$definition instanceof ContainerInterface) {
                continue;
            }
            $this->injectServiceContainerIntoDataDrivers($definition->getDataProviderDefinition(), $environment);
        }
    }

    /**
     * Inject the service container into the data driver instances.
     *
     * @param DataProviderDefinitionInterface $providerDefinitions The definitions.
     * @param EnvironmentInterface            $environment         The environment containing the providers.
     *
     * @return void
     */
    private function injectServiceContainerIntoDataDrivers($providerDefinitions, $environment)
    {
        foreach ($providerDefinitions as $provider) {
            $providerInstance = $environment->getDataProvider($provider->getName());
            if ($providerInstance instanceof Driver) {
                $initialization = $provider->getInitializationData();
                $metaModel      = $this->factory->getMetaModel($initialization['source']);
                $providerInstance->setBaseConfig(
                    array_merge($initialization, ['metaModel' => $metaModel])
                );
                $providerInstance->setDispatcher($this->dispatcher);
                $providerInstance->setConnection($this->connection);
            }
        }
    }
}
