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

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IMetaModel;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
final readonly class FilterSettingAttributeInstanceOfCondition implements PropertyConditionInterface
{
    public function __construct(
        private IFilterSettingFactory $filterFactory,
        private string $attributeBaseClass,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if (!$model) {
            return false;
        }
        $attrId = $model->getProperty('attr_id');
        if (!$attrId) {
            return false;
        }
        $metaModel = $this->getMetaModel($model);
        $attribute = $metaModel->getAttributeById((int) $attrId);

        if (!$attribute) {
            return false;
        }

        return $attribute instanceof $this->attributeBaseClass;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __clone()
    {
    }

    private function getMetaModel(ModelInterface $model): IMetaModel
    {
        $filterSetting = $this->filterFactory->createCollection($model->getProperty('fid'));

        return $filterSetting->getMetaModel();
    }
}
