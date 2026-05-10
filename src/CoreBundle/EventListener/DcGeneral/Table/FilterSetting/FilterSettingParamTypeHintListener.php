<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use Contao\Message;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Displays a deprecation hint when a filter rule uses param_type "slugNget".
 *
 * The value "slugNget" is kept for backward compatibility with legacy MetaModels but will be removed in a future
 * version. This listener informs the editor to switch to either "slug" or "get".
 *
 * The hint fires on BuildWidgetEvent (not PreEditModelEvent) so it only appears when the param_type field is
 * actually visible in the active palette — i.e. for filter types that expose this setting.
 */
final class FilterSettingParamTypeHintListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, TranslatorInterface $translator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->translator        = $translator;
    }

    /**
     * Add a deprecation hint when the param_type widget is rendered with value "slugNget".
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildWidgetEvent $event): void
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if ('tl_metamodel_filtersetting' !== $dataDefinition->getName()) {
            return;
        }

        if ('param_type' !== $event->getProperty()->getName()) {
            return;
        }

        if ('slugNget' !== $event->getModel()->getProperty('param_type')) {
            return;
        }

        Message::addInfo($this->translator->trans('hint_param_type_slugNget', [], 'tl_metamodel_filtersetting'));
    }
}
