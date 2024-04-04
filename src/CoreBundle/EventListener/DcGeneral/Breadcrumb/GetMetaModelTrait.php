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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use MetaModels\IFactory;
use MetaModels\IMetaModel;

/**
 * This trait provides access to MetaModel instances.
 */
trait GetMetaModelTrait
{
    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Retrieve the MetaModel instance.
     *
     * @param string $metaModelId The MetaModel id.
     *
     * @return IMetaModel
     *
     * @throws \RuntimeException Throws if no factory set.
     */
    protected function getMetaModel($metaModelId)
    {
        $metaModelName = $this->factory->translateIdToMetaModelName($metaModelId);

        $metaModel = $this->factory->getMetaModel($metaModelName);

        if (null === $metaModel) {
            throw new \RuntimeException('MetaModel not found');
        }

        return $metaModel;
    }

    /**
     * {@inheritDoc}
     */
    public function setMetaModelFactory(IFactory $factory): void
    {
        $this->factory = $factory;
    }
}
