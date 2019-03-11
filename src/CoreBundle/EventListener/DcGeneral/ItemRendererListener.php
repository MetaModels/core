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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Alexander Menk <a.menk@imi.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\IItem;
use MetaModels\Items;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Setting\IRenderSettingFactory;
use MetaModels\Render\Template;

/**
 * Render a MetaModel item in the backend using the render setting attached to the active input screen.
 */
class ItemRendererListener
{
    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * Create a new instance.
     *
     * @param IRenderSettingFactory $renderSettingFactory The render setting factory.
     */
    public function __construct(IRenderSettingFactory $renderSettingFactory)
    {
        $this->renderSettingFactory = $renderSettingFactory;
    }

    /**
     * Render the current item using the specified render setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function render(ModelToLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        /** @var IMetaModelDataDefinition $definition */
        $definition = $environment->getDataDefinition();
        /** @var Contao2BackendViewDefinitionInterface $viewSection */
        $viewSection = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $listing     = $viewSection->getListingConfig();

        /** @var Model $model */
        $model = $event->getModel();

        if (!($model instanceof Model)) {
            return;
        }

        $nativeItem = $model->getItem();
        $metaModel  = $nativeItem->getMetaModel();

        $renderSetting = $this->renderSettingFactory
            ->createCollection($metaModel, $definition->getMetaModelDefinition()->getActiveRenderSetting());

        if (!$renderSetting) {
            return;
        }

        $data = array($nativeItem->parseValue('html5', $renderSetting));

        if ($listing->getShowColumns()) {
            $event->setArgs($data[0]['html5']);
            return;
        }

        $template      = new Template($renderSetting->get('template'));
        $renderSetting = self::removeInvariantAttributes($nativeItem, $renderSetting);

        $template->setData(
            array(
                'settings' => $renderSetting,
                'items'    => new Items(array($nativeItem)),
                'view'     => $renderSetting,
                'data'     => $data
            )
        );

        $event->setLabel('%s')->setArgs(array($template->parse('html5')));
    }

    /**
     * Render a model for use in a group header.
     *
     * @param RenderReadablePropertyValueEvent $event The event.
     *
     * @return void
     */
    public function getReadableValue(RenderReadablePropertyValueEvent $event)
    {
        $environment = $event->getEnvironment();
        /** @var IMetaModelDataDefinition $definition */
        $definition = $environment->getDataDefinition();

        /** @var Model $model */
        $model = $event->getModel();

        if (!($model instanceof Model)) {
            return;
        }

        $nativeItem = $model->getItem();
        $metaModel  = $nativeItem->getMetaModel();

        $renderSetting = $this->renderSettingFactory->createCollection(
            $metaModel,
            $definition->getMetaModelDefinition()->getActiveRenderSetting()
        );

        if (!$renderSetting) {
            return;
        }

        $result = $nativeItem->parseAttribute($event->getProperty()->getName(), 'text', $renderSetting);

        if (!isset($result['text'])) {
            $event->setRendered(
                sprintf(
                    'Unexpected behaviour, attribute %s text representation was not rendered.',
                    $event->getProperty()->getName()
                )
            );
            return;
        }

        $event->setRendered($result['text']);
    }

    /**
     * Add additional parent header fields.
     *
     * @param GetParentHeaderEvent $event The subscribed event.
     *
     * @return void
     */
    public function addAdditionalParentHeaderFields(GetParentHeaderEvent $event)
    {
        $parentModel = $event->getModel();

        if (!$parentModel instanceof Model) {
            return;
        }
        $environment = $event->getEnvironment();
        /** @var IMetaModelDataDefinition $definition */
        $definition = $environment->getDataDefinition();

        $item          = $parentModel->getItem();
        $metaModel     = $item->getMetaModel();
        $renderSetting = $this->renderSettingFactory->createCollection(
            $metaModel,
            $definition->getMetaModelDefinition()->getActiveRenderSetting()
        );
        $additional    = array();

        foreach ($renderSetting->getSettingNames() as $name) {
            $parsed = $item->parseAttribute($name, 'text', $renderSetting);
            $name   = $item->getAttribute($name)->getName();

            $additional[$name] = $parsed['text'];
        }

        $additional = array_merge(
            $additional,
            $event->getAdditional()
        );

        $event->setAdditional($additional);
    }

    /**
     * Remove invariant attributes from the render setting.
     *
     * This is done by cloning the input collection of render settings and removing any invariant attribute.
     *
     * @param IItem       $nativeItem    The native item.
     *
     * @param ICollection $renderSetting The render setting to be used.
     *
     * @return ICollection
     */
    private function removeInvariantAttributes(IItem $nativeItem, ICollection $renderSetting)
    {
        $model = $nativeItem->getMetaModel();

        if ($model->hasVariants() && !$nativeItem->isVariantBase()) {
            // Create a clone to have a separate copy of the object as we are going to manipulate it here.
            $renderSetting = clone $renderSetting;

            // Loop over all attributes and remove those from rendering that are not desired.
            foreach (array_keys($model->getInVariantAttributes()) as $strAttrName) {
                $renderSetting->setSetting($strAttrName, null);
            }
        }

        return $renderSetting;
    }
}
