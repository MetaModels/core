<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2022 The MetaModels team.
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
 * Class FilterSettingTypeSubPaletteCondition
 */
final class FilterSettingTypeSubPaletteCondition implements PropertyConditionInterface
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

    /**
     * The filter type name.
     *
     * @var string
     */
    private $filterType;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     * @param string                $filterType    The filter type name.
     */
    public function __construct(IFilterSettingFactory $filterFactory, $filterType)
    {
        $this->filterFactory = $filterFactory;
        $this->filterType    = $filterType;
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if (!$model || !$model->getProperty('attr_id')) {
            return false;
        }

        $metaModel = $this->getMetaModel($model);
        $attribute = $metaModel->getAttributeById((int) $model->getProperty('attr_id'));

        if (!$attribute) {
            return false;
        }

        return $attribute->get('type') === $this->filterType;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }

    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param ModelInterface $model The model for which to retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    private function getMetaModel(ModelInterface $model)
    {
        $filterSetting = $this->filterFactory->createCollection($model->getProperty('fid'));

        return $filterSetting->getMetaModel();
    }
}
