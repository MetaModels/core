<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels;

use MetaModels\Events\CollectMetaModelTableNamesEvent;
use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\Events\GetMetaModelNameFromIdEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, call @link{MetaModelFactory::getMetaModel()}
 */
class Factory implements IFactory
{
    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * The already translated MetaModel names.
     *
     * @var string[]
     */
    private $lookupMap = array();

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container to use.
     *
     * @return Factory
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getServiceContainer()->getEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function translateIdToMetaModelName($metaModelId)
    {
        if (!isset($this->lookupMap[$metaModelId])) {
            $event = new GetMetaModelNameFromIdEvent($metaModelId);

            $this->getEventDispatcher()->dispatch($event::NAME, $event);

            $this->lookupMap[$metaModelId] = $event->getMetaModelName();
        }

        return $this->lookupMap[$metaModelId];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaModel($metaModelName)
    {
        $event = new CreateMetaModelEvent($this, $metaModelName);

        $this->getEventDispatcher()->dispatch($event::NAME, $event);

        $metaModel = $event->getMetaModel();

        if ($metaModel) {
            $attributeFactory = $this->getServiceContainer()->getAttributeFactory();
            foreach ($attributeFactory->createAttributesForMetaModel($metaModel) as $attribute) {
                $metaModel->addAttribute($attribute);
            }
        }

        return $metaModel;
    }

    /**
     * {@inheritdoc}
     */
    public function collectNames()
    {
        $event = new CollectMetaModelTableNamesEvent($this);

        $this->getEventDispatcher()->dispatch($event::NAME, $event);

        return $event->getMetaModelNames();
    }

    /**
     * Retrieve the default factory from the default container.
     *
     * @return IFactory
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getDefaultFactory()
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getFactory();
    }

    /**
     * Create a MetaModel instance from the id.
     *
     * @param int $intId The id of the MetaModel.
     *
     * @return IMetaModel the instance of the MetaModel or null if not found.
     *
     * @deprecated To create an instance use methods translateIdToMetaModelName() and getMetaModel().
     */
    public static function byId($intId)
    {
        trigger_error(
            'MetaModels\Factory::byId is deprecated and will get removed. ' .
            'Use methods MetaModels\Factory::translateIdToMetaModelName() and ' .
            'MetaModels\Factory::getMetaModel() instead.',
            E_USER_DEPRECATED
        );

        $factory = static::getDefaultFactory();
        $name    = $factory->translateIdToMetaModelName($intId);

        return $factory->getMetaModel($name);
    }

    /**
     * Create a MetaModel instance from the table name.
     *
     * @param string $strTableName The name of the table.
     *
     * @return IMetaModel the instance of the MetaModel or null if not found.
     *
     * @deprecated To create an instance use method getMetaModel().
     */
    public static function byTableName($strTableName)
    {
        trigger_error(
            'MetaModels\Factory::byTableName is deprecated and will get removed. ' .
            'Use method MetaModels\Factory::getMetaModel() instead.',
            E_USER_DEPRECATED
        );

        $factory = static::getDefaultFactory();

        return $factory->getMetaModel($strTableName);
    }

    /**
     * Query for all known MetaModel database tables.
     *
     * @return string[] all MetaModel table names as string array.
     *
     * @deprecated To retrieve all names use method collectNames().
     */
    public static function getAllTables()
    {
        trigger_error(
            'MetaModels\Factory::getAllTables is deprecated and will get removed. ' .
            'Use method MetaModels\Factory::collectNames() instead.',
            E_USER_DEPRECATED
        );

        $factory = static::getDefaultFactory();

        return $factory->collectNames();
    }
}
