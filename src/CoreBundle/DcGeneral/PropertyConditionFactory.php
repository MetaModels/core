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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\CoreBundle\DependencyInjection\IdProvidingServiceLocator;
use MetaModels\IMetaModel;

/**
 * This factory takes care of building property conditions.
 */
class PropertyConditionFactory
{
    /**
     * The factories.
     *
     * @var IdProvidingServiceLocator
     */
    private $factories;

    /**
     * The fallback factory.
     *
     * @var FallbackPropertyConditionFactory
     */
    private $fallbackFactory;

    /**
     * Create a new instance.
     *
     * @param IdProvidingServiceLocator        $factories       The factories.
     * @param FallbackPropertyConditionFactory $fallbackFactory The fallback factory.
     */
    public function __construct(IdProvidingServiceLocator $factories, FallbackPropertyConditionFactory $fallbackFactory)
    {
        $this->factories       = $factories;
        $this->fallbackFactory = $fallbackFactory;
    }

    /**
     * Obtain the list of registered type names.
     *
     * @return array
     */
    public function getTypeNames()
    {
        $names = $this->factories->ids();
        if ([] !== $fallback = $this->fallbackFactory->getIds()) {
            $names = array_unique(array_merge($fallback, $names));
        }

        return $names;
    }

    /**
     * Test if the passed type supports nesting.
     *
     * @param string $conditionType The type name.
     *
     * @return bool
     */
    public function supportsNesting($conditionType)
    {
        $factory = $this->factories->has($conditionType) ? $this->getFactory($conditionType) : null;

        return ($factory instanceof NestablePropertyConditionFactoryInterface)
            || (bool) $this->fallbackFactory->supportsNesting($conditionType);
    }

    /**
     * Get the amount of children this type supports - for unlimited, returns -1.
     *
     * @param string $conditionType The type name.
     *
     * @return int
     */
    public function maxChildren($conditionType)
    {
        $factory = $this->factories->has($conditionType) ? $this->getFactory($conditionType) : null;
        if (!$factory instanceof NestablePropertyConditionFactoryInterface) {
            if (null !== $value = $this->fallbackFactory->maxChildren($conditionType)) {
                return $value;
            }
            return 0;
        }

        return $factory->maxChildren();
    }

    /**
     * Test if an attribute type is supported for the passed condition type.
     *
     * @param string $conditionType The condition type.
     * @param string $attribute     The attribute type.
     *
     * @return bool
     */
    public function supportsAttribute($conditionType, $attribute)
    {
        $factory = $this->factories->has($conditionType) ? $this->getFactory($conditionType) : null;

        return (($factory instanceof AttributeAwarePropertyConditionFactoryInterface)
            && $factory->supportsAttribute($attribute))
            || (bool) $this->fallbackFactory->supportsAttribute($conditionType, $attribute);
    }

    /**
     * Create a condition from the passed configuration.
     *
     * @param array      $configuration The configuration.
     * @param IMetaModel $metaModel     The MetaModel instance.
     *
     * @return PropertyConditionInterface
     *
     * @throws \InvalidArgumentException When the type is unknown.
     */
    public function createCondition(array $configuration, IMetaModel $metaModel)
    {
        if (!isset($configuration['type'])) {
            throw new \InvalidArgumentException('No type given in configuration');
        }

        if (!$this->factories->has($typeName = $configuration['type'])) {
            if ($result = $this->fallbackFactory->createCondition($configuration, $metaModel)) {
                return $result;
            }

            throw new \InvalidArgumentException('Unknown type: ' . $typeName);
        }

        return $this->getFactory($typeName)->buildCondition($configuration, $metaModel);
    }

    /**
     * Fetch a factory for the passed type.
     *
     * @param string $conditionType The type name.
     *
     * @return PropertyConditionFactoryInterface
     */
    private function getFactory($conditionType)
    {
        return $this->factories->get($conditionType);
    }
}
