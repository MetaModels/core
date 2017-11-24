<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
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
    private $factory;

    /**
     * Retrieve the MetaModel instance.
     *
     * @param string $metaModelId The MetaModel id.
     *
     * @return IMetaModel
     */
    protected function getMetaModel($metaModelId)
    {
        if (null === $this->factory) {
            throw new \RuntimeException('No factory set.');
        }

        $metaModelName = $this->factory->translateIdToMetaModelName($metaModelId);
        $metaModel     = $this->factory->getMetaModel($metaModelName);

        return $metaModel;
    }

    /**
     * {@inheritDoc}
     */
    public function setMetaModelFactory(IFactory $factory)
    {
        $this->factory = $factory;
    }
}
