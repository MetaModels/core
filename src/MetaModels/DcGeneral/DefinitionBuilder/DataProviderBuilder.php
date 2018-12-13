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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\Helper\ViewCombinations;
use MetaModels\IFactory;

/**
 * This class takes care of populating the data provider instances.
 */
class DataProviderBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * The factory to use.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * Create a new instance.
     *
     * @param ViewCombinations $viewCombinations The view combinations.
     * @param IFactory         $factory          The factory.
     */
    public function __construct(ViewCombinations $viewCombinations, IFactory $factory)
    {
        $this->viewCombinations = $viewCombinations;
        $this->factory          = $factory;
    }

    /**
     * Create the data provider definition in the container if not already set.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function build(IMetaModelDataDefinition $container)
    {
        $inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());

        $config = $this->getDataProviderDefinition($container);

        // Check config if it already exists, if not, add it.
        if (!$config->hasInformation($container->getName())) {
            $providerInformation = new ContaoDataProviderInformation();
            $providerInformation->setName($container->getName());
            $config->addInformation($providerInformation);
        } else {
            $providerInformation = $config->getInformation($container->getName());
        }
        $basicDefinition = $container->getBasicDefinition();
        if ($providerInformation instanceof ContaoDataProviderInformation) {
            $providerInformation
                ->setTableName($container->getName())
                ->setClassName('MetaModels\DcGeneral\Data\Driver')
                ->setInitializationData(array(
                    'source' => $container->getName(),
                ))
                ->setVersioningEnabled(false);
            $basicDefinition->setDataProvider($container->getName());
        }

        // If in hierarchical mode, set the root provider.
        if ($basicDefinition->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL) {
            $basicDefinition->setRootDataProvider($container->getName());
        }

        // If not standalone, set the correct parent provider.
        if (!$inputScreen->isStandalone()) {
            $parentTable = $inputScreen->getParentTable();
            // Check config if it already exists, if not, add it.
            if (!$config->hasInformation($parentTable)) {
                $providerInformation = new ContaoDataProviderInformation();
                $providerInformation->setName($parentTable);
                $config->addInformation($providerInformation);
            } else {
                $providerInformation = $config->getInformation($parentTable);
            }

            if ($providerInformation instanceof ContaoDataProviderInformation) {
                $providerInformation
                    ->setTableName($parentTable)
                    ->setInitializationData(['source' => $parentTable]);
                // How can we honor other drivers? We do only check for MetaModels and legacy SQL here.
                if (in_array($parentTable, $this->factory->collectNames())) {
                    $providerInformation
                        ->setClassName('MetaModels\DcGeneral\Data\Driver');
                }

                $basicDefinition->setParentDataProvider($parentTable);
            }
        }
    }

    /**
     * Retrieve the data provider definition.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return DataProviderDefinitionInterface|DefaultDataProviderDefinition
     */
    private function getDataProviderDefinition(IMetaModelDataDefinition $container)
    {
        // Parse data provider.
        if ($container->hasDataProviderDefinition()) {
            return $container->getDataProviderDefinition();
        }

        $config = new DefaultDataProviderDefinition();
        $container->setDataProviderDefinition($config);
        return $config;
    }
}
