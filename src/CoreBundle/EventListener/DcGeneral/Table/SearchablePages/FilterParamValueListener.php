<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * Map a stored filter parameter onto the option of the language currently shown.
 *
 * The value of a predefined parameter is whatever the option list held when it was picked. With a translated
 * attribute behind the filter that value is language dependent, so opening the record with a different profile
 * language finds no matching option and the select shows the raw value as an unknown option.
 *
 * This is the counterpart of what the load callback does for content elements and modules - the same mapping,
 * only through the DC_General event instead of a DCA callback.
 *
 * @final
 */
class FilterParamValueListener extends AbstractAbstainingListener
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $settingFactory;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFilterSettingFactory    $settingFactory    The filter setting factory.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, IFilterSettingFactory $settingFactory)
    {
        parent::__construct($scopeDeterminator);
        $this->settingFactory = $settingFactory;
    }

    /**
     * Map the stored values onto the language currently shown.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(DecodePropertyValueForWidgetEvent $event): void
    {
        if (!$this->wantToHandle($event) || 'filterparams' !== $event->getProperty()) {
            return;
        }

        $values = $event->getValue();
        if (!\is_array($values) || [] === $values) {
            return;
        }

        $filterId = $event->getModel()->getProperty('filter');
        if (empty($filterId)) {
            return;
        }

        $dca    = $this->settingFactory->createCollection($filterId)->getParameterDCA();
        $mapped = $this->mapValues($values, $dca);

        if ($mapped !== $values) {
            $event->setValue($mapped);
        }
    }

    /**
     * Replace every value for which the filter setting supplied a mapping.
     *
     * @param array<string, mixed> $values The stored parameters.
     * @param array<string, mixed> $dca    The parameter DCA of the filter collection.
     *
     * @return array<string, mixed>
     */
    private function mapValues(array $values, array $dca): array
    {
        foreach ($values as $name => $parameter) {
            $current = $parameter['value'] ?? null;
            if (\is_string($current) && isset($dca[$name]['aliasMap'][$current])) {
                $values[$name]['value'] = $dca[$name]['aliasMap'][$current];
            }
        }

        return $values;
    }
}
