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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use MultiColumnWizard\Event\GetOptionsEvent;

/**
 * This provides the language name options.
 */
class LanguageOptionsListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Prepare the language options.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $event->setOptions(array_flip(array_filter(array_flip(System::getLanguages()), function ($langCode) {
            // Disable >2 char long language codes for the moment.
            return (strlen($langCode) == 2);
        })));
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param GetOptionsEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(GetOptionsEvent $event)
    {
        if ($event->getOptions() !== null) {
            return false;
        }

        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();
        if ('tl_metamodel' !== $environment->getDataDefinition()->getName()) {
            return false;
        }

        if ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName()) {
            return false;
        }

        return ('languages' === $event->getPropertyName()) && ('langcode' === $event->getSubPropertyName());
    }
}
