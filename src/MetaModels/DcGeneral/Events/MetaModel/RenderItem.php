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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\IItem;
use MetaModels\Items;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Render a MetaModel item in the backend using the render setting attached to the active input screen.
 */
class RenderItem
{
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
    protected static function removeInvariantAttributes(IItem $nativeItem, ICollection $renderSetting)
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

    /**
     * Render the current item using the specified render setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public static function render(ModelToLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        /** @var IMetaModelDataDefinition $definition */
        $definition = $environment->getDataDefinition();
        /** @var Contao2BackendViewDefinitionInterface $viewSection */
        $viewSection       = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $listing           = $viewSection->getListingConfig();

        /** @var Model $model */
        $model = $event->getModel();

        if (!($model instanceof Model)) {
            return;
        }

        $nativeItem = $model->getItem();
        $metaModel  = $nativeItem->getMetaModel();

        $renderSetting = $metaModel
            ->getServiceContainer()
            ->getRenderSettingFactory()
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

        $event->setArgs(array($template->parse('html5', true)));
    }

    /**
     * Render a model for use in a group header.
     *
     * @param RenderReadablePropertyValueEvent $event The event.
     *
     * @return void
     */
    public static function getReadableValue(RenderReadablePropertyValueEvent $event)
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

        $renderSetting = $metaModel
            ->getServiceContainer()
            ->getRenderSettingFactory()
            ->createCollection($metaModel, $definition->getMetaModelDefinition()->getActiveRenderSetting());

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
    public static function addAdditionalParentHeaderFields(GetParentHeaderEvent $event)
    {
        $parentModel = $event->getModel();

        if (!$parentModel instanceof Model) {
            return;
        }

        $item          = $parentModel->getItem();
        $metaModel     = $item->getMetaModel();
        $renderSetting = $metaModel->getServiceContainer()->getRenderSettingFactory()->createCollection($metaModel);
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
     * Register to the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return void
     */
    public static function register($dispatcher)
    {
        $dispatcher->addListener(ModelToLabelEvent::NAME, array(__CLASS__, 'render'));
        $dispatcher->addListener(RenderReadablePropertyValueEvent::NAME, array(__CLASS__, 'getReadableValue'));
        $dispatcher->addListener(GetParentHeaderEvent::NAME, array(__CLASS__, 'addAdditionalParentHeaderFields'));
    }
}
