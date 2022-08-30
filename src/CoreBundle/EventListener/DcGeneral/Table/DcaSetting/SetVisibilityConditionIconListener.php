<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;

/**
 * This handles the icon as status of visibility condition.
 */
class SetVisibilityConditionIconListener extends AbstractListener
{
    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
    }

    /**
     * Set icon as status of visibility condition.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOperationButtonEvent $event)
    {
        $environment = $event->getEnvironment();
        if ('tl_metamodel_dcasetting' !== $environment->getDataDefinition()->getName()
            || 'conditions' !== $event->getCommand()->getName()) {
            return;
        }

        $statement = $this->connection->createQueryBuilder()
            ->select('count(*) as count')
            ->from('tl_metamodel_dcasetting_condition', 't')
            ->where('settingId=:settingId')
            ->setParameter('settingId', $event->getModel()->getId())
            ->setMaxResults(1)
            ->execute()
            ->fetchFirstColumn();

        $command = $event->getCommand();
        $extra   = (array) $command->getExtra();
        $icon    = $extra['icon'];

        if (empty($statement[0])) {
            $iconDisabledSuffix = '_1';
            // Check whether icon is part of contao.
            if ($icon !== Image::getPath($icon)) {
                $iconDisabledSuffix = '_';
            }
            $icon = \substr_replace($icon, $iconDisabledSuffix, \strrpos($icon, '.'), 0);
        }

        $button = \sprintf(
            ' <a class="%s" href="%s" title="%s">%s</a>',
            $command->getName(),
            $event->getHref(),
            StringUtil::specialchars(
                \sprintf((string) $command->getDescription(), $event->getModel()->getID())
            ),
            $this->renderImageAsHtml($event, $icon, $command->getLabel())
        );

        $event->setHtml($button);

        //dump([$event, $event->getHtml(), $event->getCommand(), $extra['icon']]);
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
        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            new GenerateHtmlEvent($src, $alt),
            ContaoEvents::IMAGE_GET_HTML
        );

        return $imageEvent->getHtml();
    }
}
