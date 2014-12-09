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

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\RenderSettingAttributeIs as PropertyCondition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette\RenderSettingAttributeIs as PaletteCondition;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSetting;
use MetaModels\IMetaModel;

/**
 * Handles event operations on tl_metamodel_rendersetting.
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
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')) {
                        return;
                    }
                    $subscriber = new BreadCrumbRenderSetting($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'modelToLabel')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getTemplateOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getAttributeOptions')
            )
            ->addListener(
                DcGeneralEvents::ACTION,
                array($this, 'handleAddAll')
            )
            ->addListener(
                BuildDataDefinitionEvent::NAME,
                array($this, 'buildPaletteConditions')
            );
    }

    /**
     * Internal cache to speed up lookup of the MetaModels.
     *
     * Map is: [id of render setting] => IMetaModel.
     *
     * @var IMetaModel[]
     */
    protected $metaModelCache = array();

    /**
     * Retrieve the MetaModel instance from a render settings model.
     *
     * @param ModelInterface $model The model to fetch the MetaModel instance for.
     *
     * @return IMetaModel
     */
    protected function getMetaModel($model)
    {
        if (!isset($this->metaModelCache[$model->getProperty('pid')])) {
            $dbResult = $this
                ->getDatabase()
                ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
                ->execute($model->getProperty('pid'))
                ->row();

            $this->metaModelCache[$model->getProperty('pid')] = $this->getMetaModelById($dbResult['pid']);
        }

        return $this->metaModelCache[$model->getProperty('pid')];
    }

    /**
     * Draw the render setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

        $model     = $event->getModel();
        $attribute = $this->getMetaModel($model)->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $type  = $attribute->get('type');
            $image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
            if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
                $image = 'system/modules/metamodels/assets/images/icons/fields.png';
            }
            $name    = $attribute->getName();
            $colName = $attribute->getColName();
        } else {
            $translator = $event->getEnvironment()->getTranslator();
            $image      = 'system/modules/metamodels/assets/images/icons/fields.png';
            $name       = $translator->translate('error_unknown_id', 'error_unknown_attribute');
            $colName    = $translator->translate('error_unknown_column', 'error_unknown_attribute');
            $type       = $translator->translate(
                'error_unknown_id',
                'tl_metamodel_rendersettings',
                array($model->getProperty('attr_id'))
            );
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );

        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong>
                </div>')
            ->setArgs(array(
                $model->getProperty('enabled') ? 'published' : 'unpublished',
                $colName,
                $type,
                $imageEvent->getHtml(),
                $name
            ));
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getTemplateOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
            || ($event->getPropertyName() !== 'template')) {
            return;
        }

        $model          = $event->getModel();
        $parentProvider = $event->getEnvironment()->getDataProvider('tl_metamodel_rendersettings');
        $renderSettings = $parentProvider->fetch($parentProvider->getEmptyConfig()->setId($model->getProperty('pid')));
        $metaModel      = $this->getMetaModelById($renderSettings->getProperty('pid'));
        $attribute      = $metaModel->getAttributeById($model->getProperty('attr_id'));

        if (!$attribute) {
            return;
        }

        $list = new TemplateList();
        $list->setServiceContainer($this->getServiceContainer());
        $event->setOptions($list->getTemplatesForBase('mm_attr_' . $attribute->get('type')));
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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
            || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }

        $database  = $this->getDatabase();
        $model     = $event->getModel();
        $metaModel = $this->getMetaModel($model);

        if (!$metaModel) {
            return;
        }

        $arrResult = array();

        // Fetch all attributes that exist in other settings.
        $alreadyTaken = $database
            ->prepare('
            SELECT
                attr_id
            FROM
                ' . $model->getProviderName() . '
            WHERE
                attr_id<>?
                AND pid=?')
            ->execute(
                $model->getProperty('attr_id'),
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
     * @param array      $messages        The output messages.
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
                $arrData = array();

                $objRenderSetting = $attribute->getDefaultRenderSettings();
                foreach ($objRenderSetting->getKeys() as $key) {
                    $arrData[$key] = $objRenderSetting->get($key);
                }

                $arrData = array_replace_recursive(
                    $arrData,
                    array
                    (
                        'pid'      => $pid,
                        'sorting'  => $startSort,
                        'tstamp'   => time(),
                        'attr_id'  => $attribute->get('id'),
                    )
                );

                $startSort += 128;
                $database
                    ->prepare('INSERT INTO tl_metamodel_rendersetting %s')
                    ->set($arrData)
                    ->execute();
                $messages[] = array
                (
                    'severity' => 'confirm',
                    'message'  => sprintf(
                        $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_addsuccess'],
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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

        if ($event->getAction()->getName() !== 'rendersetting_addall') {
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
            new LoadLanguageFileEvent('tl_metamodel_rendersetting')
        );
        $referrer = new GetReferrerEvent(true, 'tl_metamodel_rendersetting');
        $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $referrer);

        $template = new \BackendTemplate('be_autocreatepalette');

        $template->cacheMessage  = '';
        $template->updateMessage = '';
        $template->href          = $referrer->getReferrerUrl();
        $template->headline      = $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'][1];

        // Severity is: error, confirm, info, new.
        $messages = array();

        $palette = $database
            ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
            ->execute($pid->getId());

        $metaModel = $this->getMetaModelById($palette->pid);

        $alreadyExisting = $database
            ->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE pid=?')
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
            self::perform($metaModel, $knownAttributes, $intMax, $pid->getId(), $messages);
        } else {
            // Loop over all attributes now.
            foreach ($metaModel->getAttributes() as $attribute) {
                if (array_key_exists($attribute->get('id'), $knownAttributes)) {
                    $messages[] = array
                    (
                        'severity' => 'info',
                        'message'  => sprintf(
                            $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_alreadycontained'],
                            $attribute->getName()
                        ),
                    );
                } else {
                    $messages[] = array
                    (
                        'severity' => 'confirm',
                        'message'  => sprintf(
                            $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_willadd'],
                            $attribute->getName()
                        ),
                    );

                    $blnWantPerform = true;
                }
            }
        }

        if ($blnWantPerform) {
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
    public function getLegend($name, $palette, $prevLegend = null)
    {
        if ($name[0] == '+') {
            $name = substr($name, 1);
        }

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
    public function getProperty($name, $legend)
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
    public function addCondition($property, $condition)
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
     * Apply conditions for meta palettes of the certain render setting types.
     *
     * @param LegendInterface  $legend  The legend.
     *
     * @param PaletteInterface $palette The palette.
     *
     * @return LegendInterface
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildMetaPaletteConditions($legend, $palette)
    {
        foreach ((array) $GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes'] as
                 $typeName => $paletteInfo) {
            if ($typeName == 'default') {
                continue;
            }

            if (preg_match('#^(\w+) extends (\w+)$#', $typeName, $matches)) {
                $typeName = $matches[1];
            }

            foreach ($paletteInfo as $legendName => $properties) {
                foreach ($properties as $propertyName) {
                    $condition = new PropertyCondition($typeName);
                    $legend    = self::getLegend($legendName, $palette);
                    $property  = self::getProperty($propertyName, $legend);
                    $this->addCondition($property, $condition);
                }
            }
        }

        return $legend;
    }

    /**
     * Build the data definition palettes.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function buildPaletteConditions(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

        $palettes = $event->getContainer()->getPalettesDefinition();
        $legend   = null;

        foreach ($palettes->getPalettes() as $palette) {
            if ($palette->getName() !== 'default') {
                $paletteCondition = $palette->getCondition();
                if (!($paletteCondition instanceof ConditionChainInterface)
                    || ($paletteCondition->getConjunction() !== PaletteConditionChain::OR_CONJUNCTION)
                ) {
                    $paletteCondition = new PaletteConditionChain(
                        $paletteCondition ? array($paletteCondition) : array(),
                        PaletteConditionChain::OR_CONJUNCTION
                    );
                    $palette->setCondition($paletteCondition);
                }
                $paletteCondition->addCondition(new PaletteCondition($palette->getName()));
            }

            $legend = $this->buildMetaPaletteConditions($legend, $palette);
        }
    }
}
