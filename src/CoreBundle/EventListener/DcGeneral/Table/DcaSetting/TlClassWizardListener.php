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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use MetaModels\CoreBundle\Assets\IconBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This handles the attaching of the wizard for class selection.
 */
class TlClassWizardListener extends AbstractAbstainingListener
{
    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * The URL generator.
     *
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IconBuilder              $iconBuilder       The icon builder.
     * @param UrlGeneratorInterface    $urlGenerator      The URL generator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IconBuilder $iconBuilder,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($scopeDeterminator);

        $this->iconBuilder  = $iconBuilder;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Calculate the wizard.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(ManipulateWidgetEvent $event)
    {
        if (!($this->wantToHandle($event) && ('tl_class' === $event->getProperty()->getName()))) {
            return;
        }

        $link = ' <a href="%1$s" onclick="Backend.getScrollOffset();Backend.openModalIframe({' .
            '\'width\':765,' .
            '\'title\':\'%2$s\',' .
            '\'url\':this.href,' .
            '\'id\':\'%3$s\'' .
            '});return false">%4$s</a>';

        $image = $this->iconBuilder->getBackendIconImageTag(
            'bundles/metamodelscore/images/icons/dca_wizard.png',
            $event->getEnvironment()->getTranslator()->translate('stylepicker', 'tl_metamodel_dca'),
            'style="vertical-align:top;"'
        );

        $event->getWidget()->wizard = sprintf(
            $link,
            $this->urlGenerator->generate('metamodels.picker', [
                'tbl' => $event->getEnvironment()->getDataDefinition()->getName(),
                'fld' => $event->getProperty()->getName(),
                'inputName' => 'ctrl_' . $event->getProperty()->getName(),
                'id' => $event->getModel()->getId(),
                'item' => 'PALETTE_STYLE_PICKER',
            ]),
            addslashes($event->getEnvironment()->getTranslator()->translate('stylepicker', 'tl_metamodel_dca')),
            $event->getModel()->getId(),
            $image
        );
    }
}
