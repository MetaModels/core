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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;

/**
 * This handles the rendering of models to labels.
 */
class DcaCombineButtonListener extends AbstractAbstainingListener
{
    /**
     * Clear the button if the User is not admin.
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

        $command = $event->getCommand();
        assert($command instanceof CommandInterface);

        $model = $event->getModel();
        assert($model instanceof ModelInterface);

        if ($command->getName() === 'dca_combine') {
            $event->setHref(
                UrlBuilder::fromUrl($event->getHref() ?? '')
                    ->setQueryParameter(
                        'id',
                        ModelId::fromValues('tl_metamodel_dca_combine', $model->getId())->getSerialized()
                    )
                    ->getUrl()
            );
        }
    }
}
