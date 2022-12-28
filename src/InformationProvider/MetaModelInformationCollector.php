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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\InformationProvider;

use MetaModels\Information\MetaModelCollection;
use MetaModels\Information\MetaModelCollectionInterface;
use MetaModels\Information\MetaModelInformation;

/**
 * This collects information for MetaModels.
 */
class MetaModelInformationCollector implements InformationProviderInterface
{
    /**
     * The schema collectors to use.
     *
     * @var InformationProviderInterface[]
     */
    private $providers;

    /**
     * Create a new instance.
     *
     * @param InformationProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function getNames(): array
    {
        return array_values(array_unique(array_merge(...array_map(function (InformationProviderInterface $provider) {
            return $provider->getNames();
        }, $this->providers))));
    }

    /**
     * {@inheritDoc}
     */
    public function getInformationFor(MetaModelInformation $information): void
    {
        foreach ($this->providers as $provider) {
            $provider->getInformationFor($information);
        }
    }

    /**
     * Obtain all MetaModel information.
     *
     * @return MetaModelCollectionInterface
     */
    public function getCollection(): MetaModelCollectionInterface
    {
        $collection = new MetaModelCollection();

        foreach ($this->getNames() as $name) {
            $collection->add($metaModel = new MetaModelInformation($name));
            $this->getInformationFor($metaModel);
        }

        return $collection;
    }
}
