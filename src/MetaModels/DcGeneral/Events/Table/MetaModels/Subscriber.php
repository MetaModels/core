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

namespace MetaModels\DcGeneral\Events\Table\MetaModels;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbMetaModels;
use MetaModels\Helper\TableManipulation;
use MetaModels\IMetaModel;

/**
 * Handles event operations on tl_metamodel.
 *
 * @package MetaModels\DcGeneral\Events\Table\MetaModels
 */
class Subscriber extends BaseSubscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $serviceContainer = $this->getServiceContainer();
        $this
            ->addListener(
                GetBreadcrumbEvent::NAME,
                function (GetBreadcrumbEvent $event) use ($serviceContainer) {
                    if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel') {
                        return;
                    }
                    $subscriber = new BreadCrumbMetaModels($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                GetOperationButtonEvent::NAME,
                array($this, 'getOperationButton')
            )
            ->addListener(
                GetGlobalButtonEvent::NAME,
                array($this, 'getGlobalButton')
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'modelToLabel')
            )
            ->addListener(
                PostPersistModelEvent::NAME,
                array($this, 'handleUpdate')
            )
            ->addListener(
                PreDeleteModelEvent::NAME,
                array($this, 'handleDelete')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'fixLanguageLangArray')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'unfixLanguageLangArray')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'ensureTableNamePrefix')
            );
    }

    /**
     * Clear the button if the User is not admin.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function getOperationButton(GetOperationButtonEvent $event)
    {
        if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel') {
            return;
        }

        $command = $event->getCommand();
        if ($command->getName() == 'dca_combine') {
            $event->setHref(
                UrlBuilder::fromUrl($event->getHref())
                    ->setQueryParameter(
                        'id',
                        IdSerializer::fromModel($event->getModel())
                            ->setDataProviderName('tl_metamodel_dca_combine')
                            ->getSerialized()
                    )
                    ->getUrl()
            );
        }
    }

    /**
     * Clear the button if the User is not admin.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     */
    public function getGlobalButton(GetGlobalButtonEvent $event)
    {
        if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel') {
            return;
        }

        // FIXME: direct access to BackendUser.
        if (!\BackendUser::getInstance()->isAdmin) {
            $event->setHtml('');
        }
    }

    /**
     * Render a model in the backend list.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel') {
            return;
        }

        $model      = $event->getModel();
        $translator = $event->getEnvironment()->getTranslator();
        $database   = $this->getDatabase();

        if (!($model && $database->tableExists($model->getProviderName()))) {
            return;
        }

        $strLabel = vsprintf($event->getLabel(), $event->getArgs());

        $strImage = '';
        if ($model->getProperty('addImage')) {
            $arrSize    = deserialize($model->getProperty('size'));
            $imageEvent = new ResizeImageEvent($model->getProperty('singleSRC'), $arrSize[0], $arrSize[1], $arrSize[2]);

            $event->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_RESIZE, $event);

            $strImage = sprintf(
                '<div class="image" style="padding-top:3px"><img src="%s" alt="%%1$s" /></div> ',
                $imageEvent->getImage(),
                htmlspecialchars($strLabel)
            );
        }

        $objCount = $database
            ->prepare('SELECT count(*) AS itemCount FROM ' . $model->getProperty('tableName'))
            ->execute();
        /** @noinspection PhpUndefinedFieldInspection */
        $count = $objCount->itemCount;

        $itemCount = sprintf(
            '<span style="color:#b3b3b3; padding-left:3px">[%s]</span>',
            $translator->translatePluralized(
                'itemFormatCount',
                $count,
                'tl_metamodel',
                array($count)
            )
        );
        $tableName = '<span style="color:#b3b3b3; padding-left:3px">(' . $model->getProperty('tableName') . ')</span>';

        $event->setArgs(array('<span class="name">' . $strLabel . $tableName . $itemCount . '</span>' . $strImage));
    }

    /**
     * Decode a language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function fixLangArray(DecodePropertyValueForWidgetEvent $event)
    {
        $langValues = (array) $event->getValue();
        $output     = array();
        foreach ($langValues as $langCode => $subValue) {
            if (is_array($subValue)) {
                $output[] = array_merge($subValue, array('langcode' => $langCode));
            }
        }

        $event->setValue($output);
    }

    /**
     * Encode a language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function unfixLangArray(EncodePropertyValueFromWidgetEvent $event)
    {
        $langValues  = (array) $event->getValue();
        $hasFallback = false;
        $output      = array();
        foreach ($langValues as $subValue) {
            $langCode = $subValue['langcode'];
            unset($subValue['langcode']);

            // We clear all subsequent fallbacks after we have found one.
            if ($hasFallback) {
                $subValue['isfallback'] = '';
            }

            if ($subValue['isfallback']) {
                $hasFallback = true;
            }

            $output[$langCode] = $subValue;
        }

        // If no fallback has been set, use the first language available.
        if ((!$hasFallback) && count($output)) {
            $output[$langValues[0]['langcode']]['isfallback'] = '1';
        }

        $event->setValue($output);
    }

    /**
     * Decode a language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function fixLanguageLangArray(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel')
            || ($event->getProperty() !== 'languages')) {
            return;
        }

        $this->fixLangArray($event);
    }

    /**
     * Decode a language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function unfixLanguageLangArray(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel')
            || ($event->getProperty() !== 'languages')) {
            return;
        }

        $this->unfixLangArray($event);
    }

    /**
     * Called by tl_metamodel.tableName onsave_callback.
     *
     * Prefixes the table name with mm_ if not provided by the user as such.
     * Checks if the table name is legal to the DB.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When no table name has been given.
     */
    public function ensureTableNamePrefix(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel')
            || ($event->getProperty() !== 'tableName')) {
            return;
        }

        // See #49.
        $tableName = strtolower($event->getValue());

        if (!strlen($tableName)) {
            throw new \RuntimeException('Table name not given');
        }

        // Force mm_ prefix.
        if (substr($tableName, 0, 3) !== 'mm_') {
            $tableName = 'mm_' . $tableName;
        }

        $dataProvider = $event->getEnvironment()->getDataProvider('tl_metamodel');

        // New model, ensure the table does not exist.
        if (!$event->getModel()->getId()) {
            TableManipulation::checkTableDoesNotExist($tableName);
        } else {
            // Edited model, ensure the value is unique and then that the table does not exist.
            $oldVersion = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($event->getModel()->getId()));

            if ($oldVersion->getProperty('tableName') !== $event->getModel()->getProperty('tableName')) {
                TableManipulation::checkTableDoesNotExist($tableName);
            }
        }

        $event->setValue($tableName);
    }

    /**
     * Destroy auxiliary data of attributes and delete the attributes themselves from the database.
     *
     * @param IMetaModel $metaModel The MetaModel to destroy.
     *
     * @return void
     */
    protected function destroyAttributes(IMetaModel $metaModel)
    {
        foreach ($metaModel->getAttributes() as $attribute) {
            $attribute->destroyAUX();
        }

        $this->getDatabase()
            ->prepare('DELETE FROM tl_metamodel_attribute WHERE pid=?')
            ->execute($metaModel->get('id'));
    }

    /**
     * Destroy the dca combinations for a MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel to destroy.
     *
     * @return void
     */
    protected function destroyDcaCombinations(IMetaModel $metaModel)
    {
        $this->getDatabase()
            ->prepare('DELETE FROM tl_metamodel_dca_combine WHERE pid=?')
            ->execute($metaModel->get('id'));
    }

    /**
     * Destroy the input screens for a MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel to destroy.
     *
     * @return void
     */
    protected function destroyInputScreens(IMetaModel $metaModel)
    {
        $database = $this->getDatabase();
        // Delete everything from dca settings.
        $idList = $database
            ->prepare('SELECT id FROM tl_metamodel_dca WHERE pid=?')
            ->execute($metaModel->get('id'))
            ->fetchEach('id');

        if ($idList) {
            $database
                ->prepare(
                    sprintf(
                        'DELETE FROM tl_metamodel_dcasetting WHERE pid IN (%s)',
                        implode(',', $idList)
                    )
                )
                ->execute();
        }

        // Delete the input screens.
        $database
            ->prepare('DELETE FROM tl_metamodel_dca WHERE pid=?')
            ->execute($metaModel->get('id'));
    }

    /**
     * Destroy the render settings for a MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel to destroy.
     *
     * @return void
     */
    protected function destroyRenderSettings(IMetaModel $metaModel)
    {
        $database = $this->getDatabase();
        // Delete everything from render settings.
        $arrIds = $database
            ->prepare('SELECT id FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($metaModel->get('id'))
            ->fetchEach('id');

        if ($arrIds) {
            $database
                ->prepare(
                    sprintf(
                        'DELETE FROM tl_metamodel_rendersetting WHERE pid IN (%s)',
                        implode(',', $arrIds)
                    )
                )
                ->execute();
        }

        // Delete the render settings.
        $database
            ->prepare('DELETE FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($metaModel->get('id'));
    }

    /**
     * Destroy the filter settings for a MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel to destroy.
     *
     * @return void
     */
    protected function destroyFilterSettings(IMetaModel $metaModel)
    {
        $database = $this->getDatabase();
        // Delete everything from filter settings.
        $arrIds = $database
            ->prepare('SELECT id FROM tl_metamodel_filter WHERE pid=?')
            ->execute($metaModel->get('id'))
            ->fetchEach('id');
        if ($arrIds) {
            $database
                ->prepare(
                    sprintf(
                        'DELETE FROM tl_metamodel_filtersetting WHERE pid IN (%s)',
                        implode(',', $arrIds)
                    )
                )
                ->execute();
        }
        $database
            ->prepare('DELETE FROM tl_metamodel_filter WHERE pid=?')
            ->execute($metaModel->get('id'));
    }

    /**
     * Handle the deletion of a MetaModel and all attached data.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function handleDelete(PreDeleteModelEvent $event)
    {
        if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel') {
            return;
        }

        $factory = $this->getServiceContainer()->getFactory();

        $metaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($event->getModel()->getId()));
        if ($metaModel) {
            $this->destroyAttributes($metaModel);
            $this->destroyDcaCombinations($metaModel);
            $this->destroyInputScreens($metaModel);
            $this->destroyRenderSettings($metaModel);
            $this->destroyFilterSettings($metaModel);

            TableManipulation::deleteTable($metaModel->getTableName());
        }
    }

    /**
     * Handle the update of a MetaModel and all attached data.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function handleUpdate(PostPersistModelEvent $event)
    {
        if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel') {
            return;
        }

        $old      = $event->getOriginalModel();
        $new      = $event->getModel();
        $oldTable = $old ? $old->getProperty('tableName') : null;
        $newTable = $new->getProperty('tableName');

        // Table name changed?
        if ($oldTable !== $newTable) {
            if ($oldTable && $this->getDatabase()->tableExists($oldTable, null, true)) {
                TableManipulation::renameTable($oldTable, $newTable);
                // TODO: notify attributes that the MetaModel has changed its table name.
            } else {
                TableManipulation::createTable($newTable);
            }
        }

        TableManipulation::setVariantSupport($newTable, $new->getProperty('varsupport'));
    }
}
