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

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\Dca\Helper;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\InputScreenAttributeIs;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreenSetting;
use MetaModels\IMetaModel;

/**
 * Handles event operations on tl_metamodel_dcasetting.
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
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')) {
                        return;
                    }
                    $subscriber = new BreadCrumbInputScreenSetting($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'handleModelToLabel')
            )
            ->addListener(
                DcGeneralEvents::ACTION,
                array($this, 'handleAddAll')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeLegendTitleValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeLegendTitleValue')
            )
            ->addListener(
                BuildWidgetEvent::NAME,
                array($this, 'buildLegendTitleWidget')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getAttributeOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getRichTextEditorOptions')
            )
            ->addListener(
                ManipulateWidgetEvent::NAME,
                array($this, 'getWizardForTlClass')
            )
            ->addListener(
                BuildDataDefinitionEvent::NAME,
                array($this, 'buildPaletteRestrictions')
            );
    }

    /**
     * Draw the input screen setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function drawAttribute(ModelToLabelEvent $event)
    {
        // FIXME: in here all language strings and icons are related to filters?
        // FIXME: Add language files for the error msg.
        $model        = $event->getModel();
        $objSetting   = $this
            ->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
            ->execute($model->getProperty('pid'));
        $objMetaModel = $this->getMetaModelById($objSetting->pid);

        $objAttribute = $objMetaModel->getAttributeById($model->getProperty('attr_id'));

        if ($objAttribute) {
            $type  = $objAttribute->get('type');
            $image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
            if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
                $image = 'system/modules/metamodels/assets/images/icons/fields.png';
            }
            $name    = $objAttribute->getName();
            $colName = $objAttribute->getColName();
        } else {
            $type    = 'unknown ID: ' . $model->getProperty('attr_id');
            $image   = 'system/modules/metamodels/assets/images/icons/fields.png';
            $name    = 'unknown attribute';
            $colName = 'unknown column';
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );

        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong><span class="mandatory">%s</span> <span class="tl_class">%s</span>
                </div>')
            ->setArgs(array(
                $model->getProperty('published') ? 'published' : 'unpublished',
                $colName,
                $type,
                $imageEvent->getHtml(),
                $name,
                $model->getProperty('mandatory')
                    ? ' ['.$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory'][0].']'
                    : '',
                $model->getProperty('tl_class') ? sprintf('[%s]', $model->getProperty('tl_class')) : ''
            ));
    }

    /**
     * Draw a legend.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function drawLegend(ModelToLabelEvent $event)
    {
        $model = $event->getModel();

        $arrLegend = deserialize($model->getProperty('legendtitle'));
        if (is_array($arrLegend)) {
            $strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];

            if (!$strLegend) {
                // TODO: Get the fallback language here.
                $strLegend = 'legend';
            }
        } else {
            $strLegend = $model->getProperty('legendtitle') ? $model->getProperty('legendtitle') : 'legend';
        }

        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong></div>
                <div class="dca_palette">%s%s</div>')
            ->setArgs(array(
                $model->getProperty('published') ? 'published' : 'unpublished',
                $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['legend'],
                $strLegend,
                $model->getProperty('legendhide') ? ':hide' : ''
            ));
    }

    /**
     * Render an attribute or legend.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function handleModelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')) {
            return;
        }

        $model = $event->getModel();

        switch ($model->getProperty('dcatype'))
        {
            case 'attribute':
                self::drawAttribute($event);
                break;

            case 'legend':
                self::drawLegend($event);
                break;

            default:
                break;
        }
    }

    /**
     * Perform the action.
     *
     * @param IMetaModel $metaModel       The MetaModel.
     *
     * @param array      $knownAttributes The list of known attributes.
     *
     * @param int        $startSort       The first sort index.
     *
     * @param int        $pid             The pid.
     *
     * @param array      $messages        The messages array.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function perform(IMetaModel $metaModel, $knownAttributes, $startSort, $pid, &$messages)
    {
        $database = $this->getDatabase();

        // Loop over all attributes now.
        foreach ($metaModel->getAttributes() as $attribute) {
            if (!array_key_exists($attribute->get('id'), $knownAttributes)) {
                $arrData = array
                (
                    'pid'      => $pid,
                    'sorting'  => $startSort,
                    'tstamp'   => time(),
                    'dcatype'  => 'attribute',
                    'attr_id'  => $attribute->get('id'),
                    'tl_class' => '',
                );

                $startSort += 128;
                $database
                    ->prepare('INSERT INTO tl_metamodel_dcasetting %s')
                    ->set($arrData)
                    ->execute();

                $messages[] = array
                (
                    'severity' => 'confirm',
                    'message'  => sprintf(
                        $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess'],
                        $attribute->getName()
                    ),
                );
            }
        }
    }

    /**
     * Handle the add all action event.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleAddAll(ActionEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')) {
            return;
        }

        if ($event->getAction()->getName() !== 'dca_addall') {
            return;
        }

        $environment = $event->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        $database    = $this->getDatabase();
        $input       = $environment->getInputProvider();
        $pid         = IdSerializer::fromSerialized($input->getParameter('pid'));

        $event->getAction()->getName();

        $dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, new LoadLanguageFileEvent('default'));
        $dispatcher->dispatch(
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
            new LoadLanguageFileEvent('tl_metamodel_dcasetting')
        );
        $referrer = new GetReferrerEvent(true, 'tl_metamodel_dcasetting');
        $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $referrer);

        $template = new \BackendTemplate('be_autocreatepalette');

        $template->cacheMessage  = '';
        $template->updateMessage = '';
        $template->href          = $referrer->getReferrerUrl();
        $template->headline      = $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1];

        // Severity is: error, confirm, info, new.
        $messages = array();

        $palette = $database
            ->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
            ->execute($pid->getId());

        $metaModel = $this->getMetaModelById($palette->pid);

        $alreadyExisting = $database
            ->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=?')
            ->execute($pid->getId());

        $knownAttributes = array();
        $intMax          = 128;
        while ($alreadyExisting->next()) {
            $knownAttributes[$alreadyExisting->attr_id] = $alreadyExisting->row();
            if ($intMax < $alreadyExisting->sorting) {
                $intMax = $alreadyExisting->sorting;
            }
        }

        $blnWantPerform = false;
        // Perform the labour work.
        if ($input->getValue('act') == 'perform') {
            self::perform(
                $metaModel,
                $knownAttributes,
                $intMax,
                $pid->getId(),
                $messages
            );
        } else {
            // Loop over all attributes now.
            foreach ($metaModel->getAttributes() as $attribute) {
                if (array_key_exists($attribute->get('id'), $knownAttributes)) {
                    $messages[] = array
                    (
                        'severity' => 'info',
                        'message'  => sprintf(
                            $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained'],
                            $attribute->getName()
                        ),
                    );
                } else {
                    $messages[] = array
                    (
                        'severity' => 'confirm',
                        'message'  => sprintf(
                            $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd'],
                            $attribute->getName()
                        ),
                    );

                    $blnWantPerform = true;
                }
            }
        }

        if ($blnWantPerform) {
            // FIXME: need environment here.
            $template->action = ampersand(\Environment::getInstance()->request);
            $template->submit = $GLOBALS['TL_LANG']['MSC']['continue'];
        } else {
            $template->action = ampersand($referrer->getReferrerUrl());
            $template->submit = $GLOBALS['TL_LANG']['MSC']['saveNclose'];
        }

        $template->error = $messages;

        $event->setResponse($template->parse());
    }

    /**
     * Retrieve the MetaModel the given model is attached to.
     *
     * @param ModelInterface $model The input screen model for which to retrieve the MetaModel.
     *
     * @return IMetaModel
     *
     * @throws DcGeneralInvalidArgumentException When an invalid model has been passed or the model does not have an id.
     */
    protected function getMetaModelFromModel(ModelInterface $model)
    {
        if (!(($model->getProviderName() == 'tl_metamodel_dcasetting') && $model->getProperty('pid'))) {
            throw new DcGeneralInvalidArgumentException(
                sprintf(
                    'Model must originate from tl_metamodel_dcasetting and be saved, this one originates from %s and ' .
                    'has pid %s',
                    $model->getProviderName(),
                    $model->getProperty('pid')
                )
            );
        }

        $metaModelId = $this
            ->getDatabase()
            ->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
            ->execute($model->getProperty('pid'));

        return $this->getMetaModelById($metaModelId->pid);
    }

    /**
     * Decode the title value.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeLegendTitleValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')
            || ($event->getProperty() !== 'legendtitle')) {
            return;
        }

        $metaModel = $this->getMetaModelFromModel($event->getModel());

        $values = Helper::decodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Encode the title value.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeLegendTitleValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')
            || ($event->getProperty() !== 'legendtitle')) {
            return;
        }

        $metaModel = $this->getMetaModelFromModel($event->getModel());

        $values = Helper::encodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Generate the widget.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildLegendTitleWidget(BuildWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')
            || ($event->getProperty() !== 'legendtitle')) {
            return;
        }

        $metaModel = $this->getMetaModelFromModel($event->getModel());

        Helper::prepareLanguageAwareWidget(
            $event->getEnvironment(),
            $event->getProperty(),
            $metaModel,
            $event->getEnvironment()->getTranslator()->translate('name_langcode', 'tl_metamodel_dcasetting'),
            $event->getEnvironment()->getTranslator()->translate('name_value', 'tl_metamodel_dcasetting'),
            false,
            deserialize($event->getModel()->getProperty('legendtitle'))
        );
    }

    /**
     * Retrieve the options for the attributes.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getAttributeOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')
            || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModelFromModel($model);

        if (!$metaModel) {
            return;
        }

        $arrResult = array();

        // Fetch all attributes that exist in other settings.
        $alreadyTaken = $this
            ->getDatabase()
            ->prepare('
            SELECT
                attr_id
            FROM
                tl_metamodel_dcasetting
            WHERE
                attr_id<>?
                AND pid=?
                AND dcatype="attribute"')
            ->execute(
                $model->getProperty('attr_id') ?: 0,
                $model->getProperty('pid')
            )
            ->fetchEach('attr_id');

        foreach ($metaModel->getAttributes() as $attribute) {
            if (in_array($attribute->get('id'), $alreadyTaken)) {
                continue;
            }
            $arrResult[$attribute->get('id')] = sprintf(
                '%s [%s]',
                $attribute->getName(),
                $attribute->get('type')
            );
        }

        $event->setOptions($arrResult);
    }

    /**
     * Retrieve the options for rich text editor configuration.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getRichTextEditorOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')
            || ($event->getPropertyName() !== 'rte')) {
            return;
        }

        $configs = array();
        foreach (glob(TL_ROOT . '/system/config/tiny*.php') as $name) {
            $name = basename($name);
            if ((strpos($name, 'tiny') === 0) && (substr($name, -4, 4) == '.php')) {
                $configs[] = substr($name, 0, -4);
            }
        }
        $event->setOptions($configs);
    }

    /**
     * Build the wizard string.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public function getWizardForTlClass(ManipulateWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting')
            || ($event->getProperty()->getName() !== 'tl_class')) {
            return;
        }

        $url = 'system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_STYLE_PICKER';
        if (version_compare(VERSION, '3.0', '<')) {
            $link = ' <a href="' . $url . '" data-lightbox="files 768 80%%">%s</a>';
        } else {
            $link = ' <a href="javascript:Backend.openModalIframe({url:\'' .
                $url .
                '\',width:790,title:\'Stylepicker\'});">%s</a>';
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'system/modules/metamodels/assets/images/icons/dca_wizard.png',
                $event->getEnvironment()->getTranslator()->translate('stylepicker', 'tl_metamodel_dcasetting'),
                'style="vertical-align:top;"'
            )
        );

        $event->getWidget()->wizard = sprintf(
            $link,
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->getProperty()->getName(),
            $event->getProperty()->getName(),
            $event->getModel()->getId(),
            $imageEvent->getHtml()
        );
    }

    /**
     * Retrieve the legend with the given name.
     *
     * @param string           $name       Name of the legend.
     *
     * @param PaletteInterface $palette    The palette.
     *
     * @param LegendInterface  $prevLegend The previous legend.
     *
     * @return LegendInterface
     */
    protected function getLegend($name, $palette, $prevLegend = null)
    {
        if (!$palette->hasLegend($name)) {
            $palette->addLegend(new Legend($name), $prevLegend);
        }

        return $palette->getLegend($name);
    }

    /**
     * Retrieve a property from a legend or create a new one.
     *
     * @param string          $name   The legend name.
     *
     * @param LegendInterface $legend The legend instance.
     *
     * @return PropertyInterface
     */
    protected function getProperty($name, $legend)
    {
        foreach ($legend->getProperties() as $property) {
            if ($property->getName() == $name) {
                return $property;
            }
        }

        $property = new Property($name);
        $legend->addProperty($property);

        return $property;
    }

    /**
     * Add a condition to a property.
     *
     * @param PropertyInterface  $property  The property.
     *
     * @param ConditionInterface $condition The condition to add.
     *
     * @return void
     */
    protected function addCondition($property, $condition)
    {
        $currentCondition = $property->getVisibleCondition();
        if ((!($currentCondition instanceof ConditionChainInterface))
            || ($currentCondition->getConjunction() != ConditionChainInterface::OR_CONJUNCTION)
        ) {
            if ($currentCondition === null) {
                $currentCondition = new PropertyConditionChain(array($condition));
            } else {
                $currentCondition = new PropertyConditionChain(array($currentCondition, $condition));
            }
            $currentCondition->setConjunction(ConditionChainInterface::OR_CONJUNCTION);
            $property->setVisibleCondition($currentCondition);
        } else {
            $currentCondition->addCondition($condition);
        }
    }

    /**
     * Build the data definition palettes.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function buildPaletteRestrictions(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_dcasetting')) {
            return;
        }

        $palettes = $event->getContainer()->getPalettesDefinition();
        $legend   = null;

        foreach ($palettes->getPalettes() as $palette) {
            $condition = new PropertyValueCondition('dcatype', 'attribute');
            $legend    = $this->getLegend('functions', $palette, $legend);
            $property  = $this->getProperty('readonly', $legend);
            $this->addCondition($property, $condition);
            $legend   = $this->getLegend('title', $palette, $legend);
            $property = $this->getProperty('attr_id', $legend);
            $this->addCondition($property, $condition);

            $condition = new PropertyValueCondition('dcatype', 'legend');
            $legend    = $this->getLegend('title', $palette);
            $property  = $this->getProperty('legendtitle', $legend);

            $this->addCondition($property, $condition);
            $property = $this->getProperty('legendhide', $legend);
            $this->addCondition($property, $condition);

            if (!isset($GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'])) {
                continue;
            }

            foreach ((array) $GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'] as
                     $typeName => $paletteInfo) {
                foreach ($paletteInfo as $legendName => $properties) {
                    foreach ($properties as $propertyName) {
                        $condition = new InputScreenAttributeIs($typeName);
                        $legend    = $this->getLegend($legendName, $palette);
                        $property  = $this->getProperty($propertyName, $legend);
                        $this->addCondition($property, $condition);
                    }
                }
            }
        }
    }
}
