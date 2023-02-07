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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;

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
    private RequestScopeDeterminator $scopeDeterminator;

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
    public function handle(GetOptionsEvent $event): void
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $languages           = System::getLanguages();
        $hasTerritorySupport = (bool) $event->getModel()->getProperty('localeterritorysupport');
        $languageOptions     = [];

        foreach ($languages as $langKey => $langValue) {
            $hasTerritory = (2 < \strlen($langKey));
            if (!$hasTerritorySupport && $hasTerritory) {
                continue;
            }

            $languageOptions[$langKey] = \sprintf(
                '%s [%s]',
                $langValue,
                $langKey
            );
        }

        $event->setOptions($languageOptions);
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param GetOptionsEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(GetOptionsEvent $event): bool
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
