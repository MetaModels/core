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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\BackendIntegration\TemplateList;

/**
 * This handles the providing of available templates.
 */
class TemplateOptionListener extends AbstractAbstainingListener
{
    /**
     * The template list provider.
     *
     * @var TemplateList
     */
    private TemplateList $templateList;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param TemplateList             $templateList      The template list provider.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, TemplateList $templateList)
    {
        parent::__construct($scopeDeterminator);
        $this->templateList = $templateList;
    }

    /**
     * Retrieve the options for the attributes.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getPropertyName() !== 'template')) {
            return;
        }

        $event->setOptions($this->templateList->getTemplatesForBase('metamodel_'));
    }
}
