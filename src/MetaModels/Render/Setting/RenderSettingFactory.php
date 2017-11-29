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
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Render\Setting;

use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use MetaModels\Render\Setting\Events\CreateRenderSettingFactoryEvent;

/**
 * This is the filter settings factory interface.
 */
class RenderSettingFactory implements IRenderSettingFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * The already created render settings.
     *
     * @var ICollection[]
     */
    protected $renderSettings;

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container to use.
     *
     * @return RenderSettingFactory
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        $this->serviceContainer->getEventDispatcher()->dispatch(
            MetaModelsEvents::RENDER_SETTING_FACTORY_CREATE,
            new CreateRenderSettingFactoryEvent($this)
        );
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
     * Collect the attribute settings for the given render setting.
     *
     * @param IMetaModel  $metaModel     The MetaModel instance to retrieve the settings for.
     *
     * @param ICollection $renderSetting The render setting.
     *
     * @return void
     */
    public function collectAttributeSettings(IMetaModel $metaModel, $renderSetting)
    {
        $attributeRow = $this->serviceContainer->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE pid=? AND enabled=1 ORDER BY sorting')
            ->execute($renderSetting->get('id'));

        while ($attributeRow->next()) {
            $attribute = $metaModel->getAttributeById($attributeRow->attr_id);
            if (!$attribute) {
                continue;
            }

            // TODO: we should provide attribute type based render setting elements in version 2.X.
            $attributeSetting = $renderSetting->getSetting($attribute->getColName());
            if (!$attributeSetting) {
                $attributeSetting = $attribute->getDefaultRenderSettings();
            }

            foreach ($attributeRow->row() as $strKey => $varValue) {
                if ($varValue) {
                    $attributeSetting->set($strKey, deserialize($varValue));
                }
            }
            $renderSetting->setSetting($attribute->getColName(), $attributeSetting);
        }
    }

    /**
     * Create a ICollection instance from the id.
     *
     * @param IMetaModel $metaModel The MetaModel for which to retrieve the render setting.
     *
     * @param string     $settingId The id of the ICollection.
     *
     * @return ICollection The instance or null if not found.
     */
    protected function internalCreateRenderSetting(IMetaModel $metaModel, $settingId)
    {
        $row = $this->serviceContainer->getDatabase()
            ->prepare(
                'SELECT * FROM tl_metamodel_rendersettings WHERE pid=? AND id=?'
            )
            ->limit(1)
            ->execute($metaModel->get('id'), $settingId ?: 0);

        /** @noinspection PhpUndefinedFieldInspection */
        if (!$row->numRows) {
            $row = null;
        }

        $renderSetting = new Collection($metaModel, $row ? $row->row() : array());

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
