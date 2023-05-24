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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use MetaModels\Attribute\IInternal;
use MetaModels\DcGeneral\Data\Model;

final class ManipulateWidgetListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator
    ) {
        $this->scopeDeterminator = $scopeDeterminator;
    }
    /**
     * Change the widget template with your own choice.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(ManipulateWidgetEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $model = $event->getModel();
        if (!$model instanceof Model) {
            return;
        }

        $property = $event->getProperty();
        if (null === $attribute = $model->getItem()->getMetaModel()->getAttribute($property->getName())) {
            return;
        }

        // Check virtual types.
        if ($attribute instanceof IInternal) {
            return;
        }

        if (!\in_array('be_template', $attribute->getAttributeSettingNames(), true)) {
            return;
        }

        $propExtra = $property->getExtra();

        if (null !== ($template = $propExtra['be_template'] ?? null)) {
            $event->getWidget()->template = $template;
        }
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event): bool
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();

        if ('mm_' !== \substr($environment->getDataDefinition()->getName(), 0, 3)) {
            return false;
        }

        return true;
    }
}
