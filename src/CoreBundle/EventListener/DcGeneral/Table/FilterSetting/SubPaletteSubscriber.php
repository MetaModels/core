<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IMetaModel;

/**
 * This takes care of injecting the sub palettes.
 */
class SubPaletteSubscriber
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

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
     * Prepares the sub palettes e. g. add option for translated attributes for different filter types.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function prepareSubPalettes(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }

        $model      = $event->getModel();
        $metaModel  = $this->getMetaModel($model);
        $typeName   = $model->getProperty('type');
        $palettes   = $event->getEnvironment()->getDataDefinition()->getPalettesDefinition();
        $properties = $event->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        if (!$palettes->hasPaletteByName($typeName)) {
            return;
        }

        if (!isset($GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$typeName . '_palettes'])) {
            return;
        }
        $typeLegends = $GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$typeName . '_palettes'];
        foreach ($metaModel->getAttributes() as $attribute) {
            $typeName = $attribute->get('type');
            if (empty($typeLegends[$typeName])) {
                continue;
            }
            $this->prepareIncludeLegend($typeLegends[$typeName], $properties, $palettes->getPaletteByName($typeName));
        }
    }

    /**
     * Prepare the conditions for the passed include legend.
     *
     * @param array                         $includeLegend The legend properties.
     * @param PropertiesDefinitionInterface $properties    The property definitions.
     * @param PaletteInterface              $palette       The palette to manipulate.
     *
     * @return void
     */
    private function prepareIncludeLegend(
        array $includeLegend,
        PropertiesDefinitionInterface $properties,
        PaletteInterface $palette
    ) {
        foreach ($includeLegend as $includeLegendName => $includeProperties) {
            foreach ($includeProperties as $includeProperty) {
                if ((false === $properties->hasProperty($includeProperty))
                    || (false === $palette->hasLegend($includeLegendName))
                ) {
                    continue;
                }

                $legend = $palette->getLegend($includeLegendName);
                if (true === $legend->hasProperty($includeProperty)) {
                    continue;
                }

                $legend->addProperty($paletteProperty                   = new Property($includeProperty));
                $paletteProperty->setVisibleCondition($visibleCondition = new PropertyConditionChain());
                $visibleCondition->addCondition(new BooleanCondition(true));
            }
        }
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
