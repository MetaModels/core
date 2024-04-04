<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Doctrine\DBAL\Connection;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\IFactory;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the filter settings factory interface.
 *
 * @psalm-suppress DeprecatedInterface
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FilterSettingFactory implements IFilterSettingFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     *
     * @psalm-suppress DeprecatedInterface
     */
    protected $serviceContainer;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $database;

    /**
     * The registered type factories.
     *
     * @var IFilterSettingTypeFactory[]
     */
    private array $typeFactories;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Create a new instance.
     *
     * @param Connection               $database        The database connection.
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use.
     * @param IFactory                 $factory         The MetaModels factory.
     */
    public function __construct(
        Connection $database,
        EventDispatcherInterface $eventDispatcher,
        IFactory $factory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->database        = $database;
        $this->factory         = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer, bool $deprecationNotice = true)
    {
        if ($deprecationNotice) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                '"' .__METHOD__ . '" is deprecated and will get removed.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        $this->serviceContainer = $serviceContainer;

        if ($this->eventDispatcher->hasListeners(MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Event "' .
                MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE .
                '" is deprecated - register your attribute factories via the service container.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->eventDispatcher->dispatch(
                new CreateFilterSettingFactoryEvent($this),
                MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getServiceContainer()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated - use the services from the service container.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return $this->serviceContainer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException When the type is already registered.
     */
    public function addTypeFactory($factory)
    {
        $typeName = $factory->getTypeName();
        if (isset($this->typeFactories[$typeName])) {
            throw new \RuntimeException('Filter type ' . $typeName . ' is already registered.');
        }
        $this->typeFactories[$typeName] = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeFactory($type)
    {
        return isset($this->typeFactories[$type]) ? $this->typeFactories[$type] : null;
    }

    /**
     * Create a new setting.
     *
     * @param array       $dbResult       The information from which to initialize the setting from.
     *
     * @param ICollection $filterSettings The MetaModel filter settings.
     *
     * @return ISimple|null
     */
    private function createSetting($dbResult, $filterSettings)
    {
        $factory = $this->getTypeFactory($dbResult['type']);
        if ($factory) {
            $setting = $factory->createInstance($dbResult, $filterSettings);

            if (!$setting) {
                return null;
            }

            // Collect next level.
            if ($factory->isNestedType()) {
                /** @var IWithChildren $setting */
                $this->collectRulesFor($setting, $filterSettings);
            }

            return $setting;
        }

        return null;
    }

    /**
     * Fetch all child rules for the given setting.
     *
     * @param IWithChildren $parentSetting  The information from which to initialize the setting from.
     * @param ICollection   $filterSettings The filter setting instance.
     *
     * @return void
     */
    private function collectRulesFor($parentSetting, $filterSettings)
    {
        $childInformation = $this->database
            ->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_filtersetting', 't')
            ->where('t.pid=:pid')
            ->andWhere('t.enabled=1')
            ->orderBy('t.sorting', 'ASC')
            ->setParameter('pid', $parentSetting->get('id'))
            ->executeQuery();

        foreach ($childInformation->fetchAllAssociative() as $item) {
            $childSetting = $this->createSetting($item, $filterSettings);
            if ($childSetting) {
                $parentSetting->addChild($childSetting);
            }
        }
    }

    /**
     * Collect the rules for a filter setting.
     *
     * @param Collection $filterSettings The filter settings instance.
     *
     * @return void
     */
    public function collectRules($filterSettings)
    {
        $information = $this->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_filtersetting', 't')
            ->where('t.fid=:fid')
            ->andWhere('t.pid=0')
            ->andWhere('t.enabled=1')
            ->orderBy('t.sorting', 'ASC')
            ->setParameter('fid', $filterSettings->get('id'))
            ->executeQuery();

        foreach ($information->fetchAllAssociative() as $item) {
            $newSetting = $this->createSetting($item, $filterSettings);
            if ($newSetting) {
                $filterSettings->addSetting($newSetting);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException When the MetaModel could not be retrieved.
     */
    public function createCollection($settingId)
    {
        $query = $this->database
            ->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_filter', 't')
            ->where('t.id=:id')
            ->setMaxResults(1)
            ->setParameter('id', $settingId)
            ->executeQuery();
        if (0 === $query->rowCount()) {
            throw new \RuntimeException('Could not retrieve filter setting');
        }

        if (false !== ($information = $query->fetchAssociative())) {
            $metaModel = $this->factory->getMetaModel($this->factory->translateIdToMetaModelName($information['pid']));
            if ($metaModel === null) {
                throw new \RuntimeException('Could not retrieve MetaModel ' . $information['pid']);
            }
            $collection = new Collection($information);

            $collection->setMetaModel($metaModel);
            $this->collectRules($collection);

            return $collection;
        }

        return new Collection([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeNames()
    {
        return \array_keys($this->typeFactories);
    }
}
