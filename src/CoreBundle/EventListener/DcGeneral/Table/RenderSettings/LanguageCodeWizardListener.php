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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use Contao\CoreBundle\Picker\PickerBuilderInterface;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;

/**
 * This handles the wizard button for the language code jump to field in the multicolumn wizard.
 */
class LanguageCodeWizardListener
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The picker builder.
     *
     * @var PickerBuilderInterface
     */
    private $pickerBuilder;

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
        if (!$this->scopeDeterminator->currentScopeIsBackend()
            || !('tl_metamodel_rendersettings' === $event->getEnvironment()->getDataDefinition()->getName())
            || !((0 === strpos($event->getProperty()->getName(), 'jumpTo'))
                 && ('[value]' === substr($event->getProperty()->getName(), -\strlen('[value]'))))
        ) {
            return;
        }

        $environment = $event->getEnvironment();

        $pickerUrl = $this->pickerBuilder->getUrl('cca_link');

        $urlEvent = new GenerateHtmlEvent(
            'pickpage.svg',
            $environment->getTranslator()->translate('MSC.pagepicker'),
            'style="vertical-middle:top;cursor:pointer"'
        );

        $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $urlEvent);

        $template = new ContaoBackendViewTemplate('dc_general_wizard_link_url_picker');
        $template
            ->set('name', $event->getWidget()->name)
            ->set('popupUrl', $pickerUrl)
            ->set('html', ' ' . $urlEvent->getHtml())
            ->set('label', $event->getProperty()->getLabel()[1])
            ->set('id', $event->getWidget()->id);

        $event->getWidget()->wizard = $template->parse();
    }
}
