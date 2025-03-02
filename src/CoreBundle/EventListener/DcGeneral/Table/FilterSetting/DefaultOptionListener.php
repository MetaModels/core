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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * This class provides the type options.
 */
class DefaultOptionListener
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     */
    public function __construct(IFilterSettingFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
            || ('defaultid' !== $event->getPropertyName())
        ) {
            return;
        }

        $model = $event->getModel();
        if (!($attributeId = $model->getProperty('attr_id'))) {
            return;
        }
        assert(\is_numeric($attributeId));

        $metaModel = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();

        if (null === $attribute = $metaModel->getAttributeById((int) $attributeId)) {
            return;
        }

        $event->setOptions($this->getOptions($attribute, (bool) $model->getProperty('onlyused')));
    }

    /**
     * Ensure that all options have a value.
     *
     * @param IAttribute $attribute The options to be cleaned.
     * @param bool       $onlyUsed  Determines if only "used" values shall be returned.
     *
     * @return array
     */
    private function getOptions($attribute, $onlyUsed)
    {
        // Remove empty values.
        $options = [];
        foreach ($attribute->getFilterOptions(null, $onlyUsed) as $key => $value) {
            // Remove html/php tags.
            $value = \trim(\strip_tags($value));

            if (!empty($value)) {
                $options[$key] = $value;
            }
        }

        return $options;
    }
}
