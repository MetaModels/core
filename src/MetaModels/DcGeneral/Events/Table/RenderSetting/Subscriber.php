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

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
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
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\Attribute\IInternal;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette\RenderSettingAttributeIs as PaletteCondition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\RenderSettingAttributeIs as PropertyCondition;
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
        $model = $event->getModel();

        if (($model->getProviderName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

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
            if ($attribute instanceof IInternal
                || in_array($attribute->get('id'), $alreadyTaken)
            ) {
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
     * @param PaletteInterface $palette The palette.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildMetaPaletteConditions($palette)
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
                    $legend    = $this->getLegend($legendName, $palette);
                    $property  = $this->getProperty($propertyName, $legend);
                    $this->addCondition($property, $condition);
                }
            }
        }
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

            $this->buildMetaPaletteConditions($palette);
        }
    }
}
