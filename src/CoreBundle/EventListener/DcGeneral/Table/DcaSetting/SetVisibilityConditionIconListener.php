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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use Contao\Image;
use Contao\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This handles the icon as status of visibility condition.
 */
class SetVisibilityConditionIconListener extends AbstractListener
{
    /**
     * Set icon as status of visibility condition.
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
            'tl_metamodel_dcasetting' !== $dataDefinition->getName()
            || 'conditions' !== $command->getName()
        ) {
            return;
        }
        $model = $event->getModel();
        assert($model instanceof ModelInterface);
        $statement = $this->connection->createQueryBuilder()
            ->select('count(*) as count')
            ->from('tl_metamodel_dcasetting_condition', 't')
            ->where('settingId=:settingId')
            ->setParameter('settingId', $model->getId())
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchFirstColumn();

        $extra   = (array) $command->getExtra();
        $icon    = (string) $extra['icon'];

        if (empty($statement[0])) {
            $iconDisabledSuffix = '_1';
            // Check whether icon is part of contao.
            if ($icon !== Image::getPath($icon)) {
                $iconDisabledSuffix = '_';
            }
            $icon = \substr_replace($icon, $iconDisabledSuffix, (int) \strrpos($icon, '.'), 0);
        }

        $button = \sprintf(
            ' <a class="%s" href="%s" title="%s">%s</a>',
            $command->getName(),
            $event->getHref() ?? '',
            StringUtil::specialchars(
                \sprintf($event->getTitle(), $model->getID())
            ),
            $this->renderImageAsHtml($event, $icon, $event->getLabel())
        );

        $event->setHtml($button);
    }

    /**
     * Render an image as HTML string.
     *
     * @param GetOperationButtonEvent $event The event.
     * @param string                  $src   The image path.
     * @param string                  $alt   An optional alt attribute.
     *
     * @return string
     */
    private function renderImageAsHtml(GetOperationButtonEvent $event, string $src, string $alt): string
    {
        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);
        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            new GenerateHtmlEvent($src, $alt),
            ContaoEvents::IMAGE_GET_HTML
        );

        return $imageEvent->getHtml() ?? '';
    }
}
