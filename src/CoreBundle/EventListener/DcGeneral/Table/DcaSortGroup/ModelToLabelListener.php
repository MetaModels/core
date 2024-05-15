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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSortGroup;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class ModelToLabelListener extends AbstractListener
{
    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);

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
            $event->setLabel(
                sprintf(
                    '%s <span style="color:#b3b3b3; padding-left:3px">[%s]</span>',
                    $event->getLabel(),
                    $this->translator->trans('fallback', [], 'metamodels_default')
                )
            );
        }
    }
}
