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

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\FilterUrlBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for simple lookup filter settings.
 */
class SimpleLookupFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

    /**
     * {@inheritDoc}
     */
    public function __construct(EventDispatcherInterface $dispatcher, FilterUrlBuilder $filterUrlBuilder)
    {
        parent::__construct();

        $this
            ->setTypeName('simplelookup')
            ->setTypeIcon('/bundles/metamodelscore/images/icons/filter_default.png')
            ->setTypeClass(SimpleLookup::class)
            ->allowAttributeTypes();

        $this->dispatcher       = $dispatcher;
        $this->filterUrlBuilder = $filterUrlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $filterSettings)
    {
        return new SimpleLookup($filterSettings, $information, $this->dispatcher, $this->filterUrlBuilder);
    }
}
