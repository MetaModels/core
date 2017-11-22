<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class ModelToLabelListener extends AbstractAbstainingListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator);

        $this->translator = $translator;
    }

    /**
     * Render the html for the input screen condition.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function handle(ModelToLabelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        if ($event->getModel()->getProperty('isdefault')) {
            $fallback = $this->translator->trans('MSC.fallback', [], 'contao_default');
            $event->setLabel(
                $event->getLabel() .
                ' <span style="color:#b3b3b3; padding-left:3px">[' . $fallback . ']</span>'
            );
        }
    }
}
