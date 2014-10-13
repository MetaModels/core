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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\Filter\Setting\Factory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Draw a filter setting in the backend.
 */
class DrawSetting
{
    /**
     * Retrieve the comment for the label.
     *
     * @param ModelInterface       $model       The filter setting to render.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @return string
     */
    public static function getLabelComment(ModelInterface $model, EnvironmentInterface $environment)
    {
        if ($model->getProperty('comment')) {
            return sprintf(
                $environment->getTranslator()->translate('typedesc._comment_', 'tl_metamodel_filtersetting'),
                specialchars($model->getProperty('comment'))
            );
        }
        return '';
    }

    /**
     * Retrieve the image for the label.
     *
     * @param ModelInterface           $model      The filter setting to render.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getLabelImage(ModelInterface $model, EventDispatcherInterface $dispatcher)
    {
        $type  = $model->getProperty('type');
        $image = $GLOBALS['METAMODELS']['filters'][$type]['image'];

        if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
            $image = 'system/modules/metamodels/assets/images/icons/filter_default.png';
        }

        if (!$model->getProperty('enabled')) {
            $intPos = strrpos($image, '.');
            if ($intPos !== false) {
                $image = substr_replace($image, '_1', $intPos, 0);
            }
        }

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            ContaoEvents::BACKEND_ADD_TO_URL,
            new AddToUrlEvent('act=edit&amp;id='.$model->getId())
        );

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );

        return sprintf(
            '<a href="%s">%s</a>',
            $urlEvent->getUrl(),
            $imageEvent->getHtml()
        );
    }

    /**
     * Retrieve the label text for a filter setting.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param ModelInterface       $model       The filter setting to render.
     *
     * @return mixed|string
     */
    public static function getLabelText(EnvironmentInterface $environment, ModelInterface $model)
    {
        $type  = $model->getProperty('type');
        $label = $environment->getTranslator()->translate('typenames.' . $type, 'tl_metamodel_filtersetting');
        if ($label == 'typenames.' . $type) {
            return $type;
        }
        return $label;
    }

    /**
     * Retrieve the label pattern.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param ModelInterface       $model       The filter setting to render.
     *
     * @return string
     */
    public static function getLabelPattern(EnvironmentInterface $environment, ModelInterface $model)
    {
        $type       = $model->getProperty('type');
        $translator = $environment->getTranslator();
        $combined   = 'typedesc.' . $type;

        if (($resultPattern = $translator->translate($combined, 'tl_metamodel_filtersetting')) == $combined) {
            $resultPattern = $translator->translate('typedesc._default_', 'tl_metamodel_filtersetting');
        }

        return $resultPattern;
    }

    /**
     * Render a model that has an attribute and url param attached.
     *
     * @param ModelToLabelEvent $event The Event.
     *
     * @return void
     */
    public static function modelToLabelWithAttributeAndUrlParam(ModelToLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $metamodel   = Factory::byId($model->getProperty('fid'))->getMetaModel();
        $attribute   = $metamodel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $attributeName = $attribute->getColName();
        } else {
            $attributeName = $model->getProperty('attr_id');
        }

        $event
            ->setLabel(self::getLabelPattern($environment, $model))
            ->setArgs(array(
                self::getLabelImage($model, $event->getDispatcher()),
                self::getLabelText($environment, $model),
                self::getLabelComment($model, $environment),
                $attributeName,
                ($model->getProperty('urlparam') ? $model->getProperty('urlparam') : $attributeName)
            ))
            ->stopPropagation();
    }

    /**
     * Fallback rendering method that renders a plain setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public static function modelToLabelDefault(ModelToLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        $model       = $event->getModel();

        $event
            ->setLabel(self::getLabelPattern($environment, $model))
            ->setArgs(array(
                self::getLabelImage($model, $event->getDispatcher()),
                self::getLabelText($environment, $model),
                self::getLabelComment($model, $environment),
                $model->getProperty('type')
            ));
    }

    /**
     * Render a filter setting into html.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public static function modelToLabel(ModelToLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $type        = $model->getProperty('type');

        // Delegate the event further to the type handlers.

        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s][%s]', $event::NAME, $environment->getDataDefinition()->getName(), $type),
            $event
        );
        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
            $event
        );

        if (!$event->isPropagationStopped()) {
            // Handle with default drawing if no one wants to handle.
            self::modelToLabelDefault($event);
        }
    }
}
