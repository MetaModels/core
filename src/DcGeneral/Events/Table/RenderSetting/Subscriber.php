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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
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
    private $metaModelCache = array();

    /**
     * Retrieve the MetaModel instance from a render settings model.
     *
     * @param ModelInterface $model The model to fetch the MetaModel instance for.
     *
     * @return IMetaModel
     */
    private function getMetaModel($model)
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
    private function getLegend($name, $palette, $prevLegend = null)
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
    private function getProperty($name, $legend)
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
    private function addCondition($property, $condition)
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
    private function buildMetaPaletteConditions($palette)
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
