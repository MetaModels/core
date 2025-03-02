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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use Contao\CoreBundle\Picker\PickerBuilderInterface;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This handles the wizard button for the language code jump to field in the multi-column wizard.
 */
class LanguageCodeWizardListener
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * The picker builder.
     *
     * @var PickerBuilderInterface
     */
    private PickerBuilderInterface $pickerBuilder;

    /**
     * LanguageCodeWizardListener constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope matcher.
     * @param PickerBuilderInterface   $pickerBuilder     The picker builder.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, PickerBuilderInterface $pickerBuilder)
    {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->pickerBuilder     = $pickerBuilder;
    }

    /**
     * Return the link picker wizard - this is called from Multi column wizard in render settings jumpTo page handling.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public function pagePicker(ManipulateWidgetEvent $event)
    {
        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        if (
            !$this->scopeDeterminator->currentScopeIsBackend()
            || !('tl_metamodel_rendersettings' === $dataDefinition->getName())
            || !((\str_starts_with($event->getProperty()->getName(), 'jumpTo'))
                 && (\str_ends_with($event->getProperty()->getName(), '[value]')))
        ) {
            return;
        }

        $pickerUrl = $this->pickerBuilder->getUrl('cca_link');

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $urlEvent = new GenerateHtmlEvent(
            'pickpage.svg',
            $translator->translate('pagePicker', 'dc-general'),
            'style="vertical-middle:top;cursor:pointer"'
        );

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($urlEvent, ContaoEvents::IMAGE_GET_HTML);

        $template = new ContaoBackendViewTemplate('dc_general_wizard_link_url_picker');
        $template
            ->set('name', $event->getWidget()->name)
            ->set('popupUrl', $pickerUrl)
            ->set('html', ' ' . (string) $urlEvent->getHtml())
            ->set('label', $translator->translate($event->getProperty()->getLabel(), $dataDefinition->getName()))
            ->set('id', $event->getWidget()->id);

        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $event->getWidget()->wizard = $template->parse();
    }
}
