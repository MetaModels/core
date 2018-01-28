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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting\AbstractFilterSettingTypeRenderer;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Handles rendering of model from tl_metamodel_filtersetting.
 *
 * @deprecated Will get removed in 3.0 - use MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting\AbstractFilterSettingTypeRenderer
 */
abstract class FilterSettingTypeRenderer extends AbstractFilterSettingTypeRenderer
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     */
    private $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
        parent::__construct(
            $serviceContainer->getFilterFactory(),
            $serviceContainer->getEventDispatcher(),
            \System::getContainer()->get('metamodels.assets.icon_builder'),
            \System::getContainer()->get('cca.dc-general.scope-matcher')
        );

        $this->getServiceContainer()->getEventDispatcher()->addListener(
            ModelToLabelEvent::NAME,
            array($this, 'modelToLabel')
        );
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}
