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

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\ITranslated;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\Events\MetaModel\BuildAttributeEvent;
use MetaModels\Helper\ViewCombinations;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class builds the property information.
 */
class PropertyDefinitionBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher       The event dispatcher.
     * @param ViewCombinations         $viewCombinations The view combinations.
     */
    public function __construct(EventDispatcherInterface $dispatcher, ViewCombinations $viewCombinations)
    {
        $this->dispatcher       = $dispatcher;
        $this->viewCombinations = $viewCombinations;
    }

    /**
     * Build the property definition.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function build(IMetaModelDataDefinition $container)
    {
        $inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());

        if ($container->hasPropertiesDefinition()) {
            $definition = $container->getPropertiesDefinition();
        } else {
            $definition = new DefaultPropertiesDefinition();
            $container->setPropertiesDefinition($definition);
        }

        $metaModel = $inputScreen->getMetaModel();

        // If the current metamodels has variants add the varbase and vargroup to the definition.
        if ($metaModel->hasVariants()) {
            // FIXME: these are not properties yet, therefore we have to work around.
            $this->getOrCreateProperty($definition, 'varbase');
            $this->getOrCreateProperty($definition, 'vargroup');
            /*
            $this->buildProperty(
                $definition,
                $metaModel->getAttribute('varbase'),
                $inputScreen->getProperty('varbase')['info']
            );
            $this->buildProperty(
                $definition,
                $metaModel->getAttribute('vargroup'),
                $inputScreen->getProperty('vargroup')['info']
            );
            */
        }

        foreach ($metaModel->getAttributes() as $attribute) {
            $this->buildProperty($definition, $attribute, $this->propInfo($inputScreen, $attribute));
            $event = new BuildAttributeEvent($metaModel, $attribute, $container, $inputScreen);
            $this->dispatcher->dispatch($event::NAME, $event);
        }
    }

    /**
     * Obtain the property info from the input screen.
     *
     * @param IInputScreen $inputScreen The input screen.
     * @param IAttribute   $attribute   The attribute.
     *
     * @return array
     */
    private function propInfo(IInputScreen $inputScreen, IAttribute $attribute)
    {
        $info = $inputScreen->getProperty($attribute->getColName());
        if (!isset($info['info'])) {
            return [];
        }

        return $info['info'];
    }

    /**
     * Build the property information for a certain property from the data container array.
     *
     * @param PropertiesDefinitionInterface $definition The property collection definition.
     * @param IAttribute                    $attribute  The attribute.
     * @param array                         $propInfo   The property info array.
     *
     * @return void
     */
    private function buildProperty(PropertiesDefinitionInterface $definition, IAttribute $attribute, array $propInfo)
    {
        if (!$attribute) {
            return;
        }

        $property = $this->getOrCreateProperty($definition, $attribute->getColName());
        $this->setLabel($property, $propInfo, $attribute);
        $this->setDescription($property, $propInfo);
        $this->setDefaultValue($property, $propInfo);
        $this->setExcluded($property, $propInfo);
        $this->setSearchable($property, $propInfo);
        $this->setFilterable($property, $propInfo);
        $this->setWidgetType($property, $propInfo);
        $this->setOptions($property, $propInfo);
        $this->setExplanation($property, $propInfo);
        $this->setEval($property, $propInfo, ($attribute instanceof ITranslated));
    }

    /**
     * Retrieves or creates a property information in the definition container.
     *
     * @param PropertiesDefinitionInterface $definition The definition container.
     * @param string                        $propName   The property name.
     *
     * @return PropertyInterface
     */
    private function getOrCreateProperty(PropertiesDefinitionInterface $definition, $propName)
    {
        if ($definition->hasProperty($propName)) {
            return $definition->getProperty($propName);
        }
        $property = new DefaultProperty($propName);
        $definition->addProperty($property);

        return $property;
    }

    /**
     * Set the label in the property.
     *
     * @param PropertyInterface $property  The property definition.
     * @param array             $propInfo  The property info array.
     * @param IAttribute        $attribute The attribute.
     *
     * @return void
     */
    private function setLabel(PropertyInterface $property, $propInfo, IAttribute $attribute)
    {
        if ($property->getLabel()) {
            return;
        }
        if (!isset($propInfo['label'])) {
            $property->setLabel($attribute->getName());
            return;
        }
        $lang = $propInfo['label'];
        if (is_array($lang)) {
            $property->setLabel(reset($lang));
            $property->setDescription(next($lang));
            return;
        }
        $property->setLabel($lang);
    }

    /**
     * Set the description in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setDescription(PropertyInterface $property, $propInfo)
    {
        if ($property->getDescription() || !isset($propInfo['description'])) {
            return;
        }

        $property->setDescription($propInfo['description']);
    }

    /**
     * Set the default value in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setDefaultValue(PropertyInterface $property, $propInfo)
    {
        if (!isset($propInfo['default'])) {
            return;
        }
        $property->setDefaultValue($propInfo['default']);
    }

    /**
     * Set the excluded flag in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setExcluded(PropertyInterface $property, $propInfo)
    {
        if (!isset($propInfo['exclude'])) {
            return;
        }
        $property->setExcluded((bool) $propInfo['exclude']);
    }

    /**
     * Set the searchable flag in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setSearchable(PropertyInterface $property, $propInfo)
    {
        if (!isset($propInfo['search'])) {
            return;
        }
        $property->setSearchable((bool) $propInfo['search']);
    }

    /**
     * Set the filterable flag in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setFilterable(PropertyInterface $property, $propInfo)
    {
        if (!isset($propInfo['filter'])) {
            return;
        }
        $property->setFilterable($propInfo['filter']);
    }

    /**
     * Set the widget type in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setWidgetType(PropertyInterface $property, $propInfo)
    {
        if (null !== $property->getWidgetType() || !isset($propInfo['inputType'])) {
            return;
        }

        $property->setWidgetType($propInfo['inputType']);
    }

    /**
     * Set the options in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setOptions(PropertyInterface $property, $propInfo)
    {
        if (null !== $property->getOptions() || !isset($propInfo['options'])) {
            return;
        }

        $property->setOptions($propInfo['options']);
    }

    /**
     * Set the explanation in the property.
     *
     * @param PropertyInterface $property The property definition.
     * @param array             $propInfo The property info array.
     *
     * @return void
     */
    private function setExplanation(PropertyInterface $property, $propInfo)
    {
        if ($property->getExplanation() || !isset($propInfo['explanation'])) {
            return;
        }

        $property->setExplanation($propInfo['explanation']);
    }

    /**
     * Set the evaluation array.
     *
     * @param PropertyInterface $property     The property definition.
     * @param array             $propInfo     The property info array.
     * @param bool              $isTranslated Flag if the MetaModel is translated.
     *
     * @return void
     */
    private function setEval($property, $propInfo, $isTranslated)
    {
        $extra = isset($propInfo['eval']) ? $propInfo['eval'] : [];
        if ($isTranslated) {
            $extra['tl_class'] = 'translat-attr' . (!empty($extra['tl_class']) ? ' ' . $extra['tl_class'] : '');
        }

        $property->setExtra(array_merge((array) $property->getExtra(), $extra));
    }
}
