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

use Database\Result;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;

/**
 * This is the filter settings factory interface.
 */
class FilterSettingFactory implements IFilterSettingFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * The registered type factories.
     *
     * @var IFilterSettingTypeFactory[]
     */
    protected $typeFactories;

    /**
     * {@inheritdoc}
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        $this->typeFactories = array();
        $this->serviceContainer->getEventDispatcher()->dispatch(
            MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE,
            new CreateFilterSettingFactoryEvent($this)
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypeFactory($factory)
    {
        $this->typeFactories[$factory->getTypeName()] = $factory;

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
     * @param Result      $dbResult       The information from which to initialize the setting from.
     *
     * @param ICollection $filterSettings The MetaModel filter settings.
     *
     * @return ISimple|null
     */
    protected function createSetting($dbResult, $filterSettings)
    {
        $factory = $this->getTypeFactory($dbResult->type);
        if ($factory) {
            $setting = $factory->createInstance($dbResult->row(), $filterSettings);

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
     *
     * @param ICollection   $filterSettings The filter setting instance.
     *
     * @return void
     */
    protected function collectRulesFor($parentSetting, $filterSettings)
    {
        $childInformation = $this->serviceContainer->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE pid=? AND enabled=1 ORDER BY sorting ASC')
            ->execute($parentSetting->get('id'));

        while ($childInformation->next()) {
            $childSetting = $this->createSetting($childInformation, $filterSettings);
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
        $database    = $this->serviceContainer->getDatabase();
        $information = $database
            ->prepare(
                'SELECT * FROM tl_metamodel_filtersetting WHERE fid=? AND pid=0 AND enabled=1 ORDER BY sorting ASC'
            )
            ->execute($filterSettings->get('id'));

        while ($information->next()) {
            $newSetting = $this->createSetting($information, $filterSettings);
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
        $information = $this->serviceContainer->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
            ->execute($settingId)
            ->row();

        if (!empty($information)) {
            $modelFactory = $this->serviceContainer->getFactory();
            $metaModel    = $modelFactory->getMetaModel($modelFactory->translateIdToMetaModelName($information['pid']));
            $collection   = new Collection($information);

            if ($metaModel === null) {
                throw new \RuntimeException('Could not retrieve MetaModel ' . $information['pid']);
            }

            $collection->setMetaModel($metaModel);
            $this->collectRules($collection);

            return $collection;
        }

        return new Collection(array());
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeNames()
    {
        return array_keys($this->typeFactories);
    }
}
