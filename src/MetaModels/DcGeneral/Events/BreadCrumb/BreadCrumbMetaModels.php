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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\IMetaModel;

/**
 * Generate a breadcrumb for table tl_metamodel.
 */
class BreadCrumbMetaModels extends BreadCrumbBase
{
    /**
     * The id of the MetaModel.
     *
     * @var int
     */
    protected $metamodelId;

    /**
     * Retrieve the MetaModel instance.
     *
     * @return IMetaModel
     */
    protected function getMetaModel()
    {
        $services      = $this->getServiceContainer();
        $modelFactory  = $services->getFactory();
        $metaModelName = $modelFactory->translateIdToMetaModelName($this->metamodelId);
        $metaModel     = $modelFactory->getMetaModel($metaModelName);

        return $metaModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        $elements[] = array(
            'url' => 'contao/main.php?do=metamodels',
            'text' => $this->getBreadcrumbLabel($environment, 'metamodels'),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/backend/logo.png'
        );

        return $elements;
    }
}
