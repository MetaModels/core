<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;

/**
 * This handles the mangling of the language array.
 */
class LanguageArrayListener extends AbstractAbstainingListener
{
    /**
     * Decode a language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function handleDecode(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'languages')) {
            return;
        }

        $langValues = (array) $event->getValue();
        $output     = [];
        foreach ($langValues as $langCode => $subValue) {
            if (is_array($subValue)) {
                $output[] = array_merge($subValue, ['langcode' => $langCode]);
            }
        }

        $event->setValue($output);
    }

    /**
     * Decode a language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function handleEncode(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'languages')) {
            return;
        }

        $langValues  = (array) $event->getValue();
        $hasFallback = false;
        $output      = [];
        foreach ($langValues as $subValue) {
            $langCode = $subValue['langcode'];
            unset($subValue['langcode']);

            // We clear all subsequent fallbacks after we have found one.
            if ($hasFallback) {
                $subValue['isfallback'] = '';
            }

            if ($subValue['isfallback']) {
                $hasFallback = true;
            }

            $output[$langCode] = $subValue;
        }

        // If no fallback has been set, use the first language available.
        if ((!$hasFallback) && count($output)) {
            $output[$langValues[0]['langcode']]['isfallback'] = '1';
        }

        $event->setValue($output);
    }
}
