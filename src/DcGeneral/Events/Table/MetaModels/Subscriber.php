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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\MetaModels;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MenAtWork\MultiColumnWizard\Event\GetOptionsEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbMetaModels;
use MetaModels\Helper\TableManipulation;

/**
 * Handles event operations on tl_metamodel.
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
                GetOptionsEvent::NAME,
                array($this, 'loadLanguageOptions')
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
                        ModelId::fromValues('tl_metamodel_dca_combine', $event->getModel()->getId())->getSerialized()
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
        $tableName  = $model->getProperty('tableName');

        if (!($model && !empty($tableName) && $database->tableExists($tableName))) {
            return;
        }

        $strLabel = vsprintf($event->getLabel(), $event->getArgs());
        $image    = ((bool) $model->getProperty('translated')) ? 'locale.png' : 'locale_1.png';
        $objCount = $database
            ->prepare('SELECT count(*) AS itemCount FROM ' . $tableName)
            ->execute();
        /** @noinspection PhpUndefinedFieldInspection */
        $count = $objCount->itemCount;

        $event->setArgs([
            sprintf(
                '
<span class="name">
  <img src="system/modules/metamodels/assets/images/icons/%1$s" /> %2$s
  <span style="color:#b3b3b3; padding-left:3px">(%3$s)</span>
  <span style="color:#b3b3b3; padding-left:3px">[%4$s]</span>
</span>',
                $image,
                $strLabel,
                $tableName,
                $translator->translatePluralized('itemFormatCount', $count, 'tl_metamodel', [$count])
            )
        ]);
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
     * Prepare the language options.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function loadLanguageOptions(GetOptionsEvent $event)
    {
        if (($event->getOptions() !== null) ||
            ($event->getModel()->getProviderName() !== 'tl_metamodel')
            || ($event->getPropertyName() !== 'languages')
            || ($event->getSubPropertyName() !== 'langcode')) {
            return;
        }

        $event->setOptions(array_flip(array_filter(array_flip(System::getLanguages()), function ($langCode) {
            // Disable >2 char long language codes for the moment.
            return (strlen($langCode) == 2);
        })));
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
     * Handle the deletion of a MetaModel and all attached data.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function handleDelete(PreDeleteModelEvent $event)
    {
        if ($event->getModel()->getProviderName() !== 'tl_metamodel') {
            return;
        }

        $factory = $this->getServiceContainer()->getFactory();

        $metaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($event->getModel()->getId()));
        if ($metaModel) {
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
