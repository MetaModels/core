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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\BackendIntegration\TemplateList;

/**
 * This handles the providing of available rich text templates.
 */
class RichTextOptionListener extends AbstractAbstainingListener
{
    /**
     * The template list provider.
     *
     * @var TemplateList
     */
    private $templateList;

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
        if (!($this->wantToHandle($event) && ($event->getPropertyName() === 'rte'))) {
            return;
        }

        $configs = $this->templateList->getTemplatesForBase('be_tiny');

        $event->setOptions($configs);
    }
}

