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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render\Setting;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use MetaModels\Render\Setting\Events\CreateRenderSettingFactoryEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the filter settings factory interface.
 */
class RenderSettingFactory implements IRenderSettingFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     *
     * @deprecated The service container will get removed.
     */
    private $serviceContainer;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $database;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

    /**
     * The already created render settings.
     *
     * @var ICollection[]
     */
    private $renderSettings;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

    /**
     * Create a new instance.
     *
     * @param Connection               $database         The database.
     * @param EventDispatcherInterface $eventDispatcher  The event dispatcher to use.
     * @param IFilterSettingFactory    $filterFactory    The filter setting factory.
     * @param FilterUrlBuilder         $filterUrlBuilder The filter URL builder.
     */
    public function __construct(
        Connection $database,
        EventDispatcherInterface $eventDispatcher,
        IFilterSettingFactory $filterFactory,
        FilterUrlBuilder $filterUrlBuilder
    ) {
        $this->eventDispatcher  = $eventDispatcher;
        $this->database         = $database;
        $this->filterFactory    = $filterFactory;
        $this->filterUrlBuilder = $filterUrlBuilder;
    }

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer  The service container to use.
     * @param bool                        $deprecationNotice Internal flag to disable deprecation notice.
     *
     * @return RenderSettingFactory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer, $deprecationNotice = true)
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

        if ($this->eventDispatcher->hasListeners(MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Event "' .
                MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE .
                '" is deprecated - register your factories via the service container.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $this->serviceContainer->getEventDispatcher()->dispatch(
                new CreateRenderSettingFactoryEvent($this),
                MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE
            );
        }

        return $this;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
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
     * Collect the attribute settings for the given render setting.
     *
     * @param IMetaModel  $metaModel     The MetaModel instance to retrieve the settings for.
     * @param ICollection $renderSetting The render setting.
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function collectAttributeSettings(IMetaModel $metaModel, $renderSetting)
    {
        $attributeRows = $this
            ->database
            ->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_rendersetting', 't')
            ->where('t.pid=:pid')
            ->andWhere('t.enabled=1')
            ->orderBy('t.sorting')
            ->setParameter('pid', $renderSetting->get('id'))
            ->executeQuery();

        foreach ($attributeRows->fetchAllAssociative() as $attributeRow) {
            $attribute = $metaModel->getAttributeById((int) $attributeRow['attr_id']);
            if (!$attribute) {
                continue;
            }

            $attributeSetting = $renderSetting->getSetting($attribute->getColName());
            if (!$attributeSetting) {
                $attributeSetting = $attribute->getDefaultRenderSettings();
            }

            foreach ($attributeRow as $strKey => $varValue) {
                if ($varValue) {
                    $attributeSetting->set($strKey, StringUtil::deserialize($varValue));
                }
            }
            $renderSetting->setSetting($attribute->getColName(), $attributeSetting);
        }
    }

    /**
     * Create a ICollection instance from the id.
     *
     * @param IMetaModel $metaModel The MetaModel for which to retrieve the render setting.
     * @param string     $settingId The id of the ICollection.
     *
     * @return ICollection The instance or null if not found.
     * @throws \Doctrine\DBAL\Exception
     */
    protected function internalCreateRenderSetting(IMetaModel $metaModel, $settingId)
    {
        $row = $this
            ->database
            ->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_rendersettings', 't')
            ->where('t.pid=:pid')
            ->andWhere('t.id=:id')
            ->setParameter('pid', $metaModel->get('id'))
            ->setParameter('id', $settingId ?: 0)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (!$row) {
            $row = [];
        }

        $renderSetting = new Collection(
            $metaModel,
            $row,
            $this->eventDispatcher,
            $this->filterFactory,
            $this->filterUrlBuilder
        );

        if ($renderSetting->get('id')) {
            $this->collectAttributeSettings($metaModel, $renderSetting);
        }

        return $renderSetting;
    }

    /**
     * {@inheritdoc}
     */
    public function createCollection(IMetaModel $metaModel, $settingId = '')
    {
        $tableName = $metaModel->getTableName();
        if (!isset($this->renderSettings[$tableName])) {
            $this->renderSettings[$tableName] = array();
        }

        if (!isset($this->renderSettings[$tableName][$settingId])) {
            $this->renderSettings[$tableName][$settingId] = $this->internalCreateRenderSetting($metaModel, $settingId);
        }

        return $this->renderSettings[$tableName][$settingId];
    }
}
