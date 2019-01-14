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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\IFactory;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This class takes care of populating the data provider instances.
 */
class DataProviderBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * The factory to use.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param ViewCombination $viewCombination The view combinations.
     * @param IFactory        $factory         The factory.
     */
    public function __construct(ViewCombination $viewCombination, IFactory $factory)
    {
        $this->viewCombination = $viewCombination;
        $this->factory         = $factory;
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
        $inputScreen = $this->viewCombination->getScreen($container->getName());
        if (!$inputScreen) {
            return;
        }
        $meta = $inputScreen['meta'];

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
                ->setClassName(Driver::class)
                ->setInitializationData(['source' => $container->getName()])
                ->setVersioningEnabled(false);
            $basicDefinition->setDataProvider($container->getName());
        }

        // If in hierarchical mode, set the root provider.
        if ($basicDefinition->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL) {
            $basicDefinition->setRootDataProvider($container->getName());
        }

        // If not standalone, set the correct parent provider.
        if ('ctable' === $meta['rendertype']) {
            $parentTable = $meta['ptable'];
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
                    $providerInformation->setClassName(Driver::class);
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
