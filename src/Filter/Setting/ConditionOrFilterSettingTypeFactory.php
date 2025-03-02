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

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\Condition\ConditionOr;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Attribute type factory for OR filter settings.
 */
class ConditionOrFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly FilterUrlBuilder $filterUrlBuilder,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();

        $this
            ->setTypeName('conditionor')
            ->setTypeIcon('bundles/metamodelscore/images/icons/filter_or.png')
            ->setTypeClass(ConditionOr::class)
            ->allowAttributeTypes();
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $filterSettings)
    {
        return new ConditionOr(
            $filterSettings,
            $information,
            $this->dispatcher,
            $this->filterUrlBuilder,
            $this->translator
        );
    }
}
