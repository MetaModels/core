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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting\AbstractFilterSettingTypeRenderer;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Handles rendering of model from tl_metamodel_filtersetting.
 *
 * @deprecated Will get removed in 3.0 -
 * use MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting\AbstractFilterSettingTypeRenderer
 */
abstract class FilterSettingTypeRenderer extends AbstractFilterSettingTypeRenderer
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     *
     * @psalm-suppress DeprecatedInterface
     */
    private IMetaModelsServiceContainer $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     *
     * @psalm-suppress DeprecatedInterface
     * @psalm-suppress DeprecatedMethod
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
        $iconBuilder = System::getContainer()->get('metamodels.assets.icon_builder');
        assert($iconBuilder instanceof IconBuilder);
        $scopeMatcher = System::getContainer()->get('cca.dc-general.scope-matcher');
        assert($scopeMatcher instanceof RequestScopeDeterminator);

        parent::__construct(
            $serviceContainer->getFilterFactory(),
            $serviceContainer->getEventDispatcher(),
            $iconBuilder,
            $scopeMatcher
        );

        $this->getServiceContainer()->getEventDispatcher()->addListener(
            ModelToLabelEvent::NAME,
            [$this, 'modelToLabel']
        );
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @psalm-suppress DeprecatedInterface
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}
