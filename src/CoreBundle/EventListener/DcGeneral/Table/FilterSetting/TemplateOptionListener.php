<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\BackendIntegration\TemplateList;

/**
 * This class provides the template options.
 */
class TemplateOptionListener
{
    /**
     * The template list.
     *
     * @var TemplateList
     */
    private TemplateList $templateList;

    /**
     * Create a new instance.
     *
     * @param TemplateList $templateList The template list provider.
     */
    public function __construct(TemplateList $templateList)
    {
        $this->templateList = $templateList;
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
            || ('template' !== $event->getPropertyName())
        ) {
            return;
        }

        $event->setOptions($this->templateList->getTemplatesForBase('mm_filteritem_'));
    }
}
