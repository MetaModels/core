<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
     * {@inheritdoc}
     */
    public function createCollection(IMetaModel $metaModel, $settingId = '')
    {
        $row = $this->serviceContainer->getDatabase()
            ->prepare(
                'SELECT * FROM tl_metamodel_rendersettings WHERE pid=? AND (id=? OR isdefault=1) ORDER BY isdefault ASC'
            )
            ->limit(1)
            ->execute($metaModel->get('id'), $settingId ?: 0);

        if (!$row->numRows) {
            $row = null;
        }

        $renderSetting = new Collection($row ? $row->row() : array());

        if ($renderSetting->get('id')) {
            $this->collectAttributeSettings($metaModel, $renderSetting);
        }

        return $renderSetting;
    }
}
