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
use MetaModels\Events\CreatePropertyConditionEvent;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the fallback to globals.
 *
 * @deprecated Only here as bc-layer to MetaModels 2.0 - to be removed in 3.0
 */
class FallbackPropertyConditionFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Obtain the id list for globally configured types.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getIds()
    {
        if (!isset($GLOBALS['METAMODELS']['inputscreen_conditions'])) {
            return [];
        }

        // @codingStandardsIgnoreStart Silencing errors is discouraged
        @trigger_error('Configuring input screen conditions via global array is deprecated. ' .
            'Please implement/configure a valid condition factory.', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        return array_keys($GLOBALS['METAMODELS']['inputscreen_conditions']);
    }

    /**
     * Test if the passed type supports nesting.
     *
     * @param string $conditionType The type name.
     *
     * @return bool|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function supportsNesting($conditionType)
    {
        if (!isset($GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['nestingAllowed'])) {
            return null;
        }

        // @codingStandardsIgnoreStart Silencing errors is discouraged
        @trigger_error('Configuring input screen conditions via global array is deprecated. ' .
            'Please implement/configure a valid condition factory.', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        return $GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['nestingAllowed'];
    }

    /**
     * Get the amount of children this type supports - for undefined returns null.
     *
     * @param string $conditionType The type name.
     *
     * @return int|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function maxChildren($conditionType)
    {
        if (!isset($GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['maxChildren'])) {
            return null;
        }

        // @codingStandardsIgnoreStart Silencing errors is discouraged
        @trigger_error('Configuring input screen conditions via global array is deprecated. ' .
            'Please implement/configure a valid condition factory.', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        return $GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['maxChildren'];
    }

    /**
     * Test if an attribute type is supported for the passed condition type.
     *
     * @param string $conditionType The condition type.
     * @param string $attribute     The attribute type.
     *
     * @return bool|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function supportsAttribute($conditionType, $attribute)
    {
        if (!isset($GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['attributes'])) {
            return null;
        }

        // @codingStandardsIgnoreStart Silencing errors is discouraged
        @trigger_error('Configuring input screen conditions via global array is deprecated. ' .
            'Please implement/configure a valid condition factory.', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $allowedAttributes = $GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['attributes'];

        return (\is_array($allowedAttributes) && !\in_array($attribute, $allowedAttributes, true));
    }

    /**
     * Create a condition from the passed configuration.
     *
     * @param array      $configuration The configuration.
     * @param IMetaModel $metaModel     The MetaModel instance.
     *
     * @return PropertyConditionInterface
     *
     * @throws \RuntimeException When the condition could not be transformed.
     */
    public function createCondition(array $configuration, IMetaModel $metaModel)
    {
        $event = new CreatePropertyConditionEvent($configuration, $metaModel);
        $this->dispatcher->dispatch(CreatePropertyConditionEvent::NAME, $event);

        if (null === $instance = $event->getInstance()) {
            throw new \RuntimeException(sprintf(
                'Condition of type %s could not be transformed to an instance.',
                $configuration['type']
            ));
        }

        // @codingStandardsIgnoreStart Silencing errors is discouraged
        @trigger_error('Creating input screen conditions via event is deprecated. ' .
            'Please implement a valid condition factory.', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        return $instance;
    }
}
