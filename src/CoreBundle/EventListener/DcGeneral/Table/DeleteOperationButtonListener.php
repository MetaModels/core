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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

use function assert;
use function in_array;
use function str_starts_with;

/**
 * This provides the attribute name options.
 */
class DeleteOperationButtonListener
{
    public function __construct(
        private readonly RequestScopeDeterminator $scopeDeterminator,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Update the delete button attributes for MetaModels tables.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOperationButtonEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $model = $event->getModel();
        assert($model instanceof ModelInterface);

        $attributes = $event->getAttributes();


        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $event->setAttributes(
            'data-msg-confirm="' . $this->translator->trans(
                'delete.confirm',
                [
                    '%id%' => $model->getID()
                ],
                $dataDefinition->getName()
            ) . '" '
            . $attributes
        );
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        /** @var GetOperationButtonEvent $event */
        $command = $event->getCommand();
        assert($command instanceof CommandInterface);

        if ('delete' !== $command->getName()) {
            return false;
        }

        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return str_starts_with($dataDefinition->getName(), 'mm_')
               || in_array(
                   $dataDefinition->getName(),
                   [
                       'tl_metamodel',
                       'tl_metamodel_attribute',
                       'tl_metamodel_dca',
                       'tl_metamodel_dca_sortgroup',
                       'tl_metamodel_dcasetting',
                       'tl_metamodel_dcasetting_condition',
                       'tl_metamodel_filter',
                       'tl_metamodel_filtersetting',
                       'tl_metamodel_rendersetting',
                       'tl_metamodel_rendersettings',
                       'tl_metamodel_searchable_pages',
                   ]
               );
    }
}
