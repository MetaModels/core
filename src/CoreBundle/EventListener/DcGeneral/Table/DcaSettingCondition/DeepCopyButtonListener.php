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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * This handles the deep copy buttons.
 */
class DeepCopyButtonListener extends AbstractConditionFactoryUsingListener
{
    /**
     * Clear the button if the User is not admin.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOperationButtonEvent $event): void
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        $command = $event->getCommand();
        assert($command instanceof CommandInterface);

        if (
            'tl_metamodel_dcasetting_condition' !== $dataDefinition->getName()
            || 'deepcopy' !== $command->getName()
        ) {
            return;
        }

        $model = $event->getModel();
        assert($model instanceof ModelInterface);

        $typeName = $model->getProperty('type');
        // If setting does not support children, disable button.
        if ($model->getId() && !$this->conditionFactory->supportsNesting($typeName)) {
            $event->setDisabled();
        }
    }
}
