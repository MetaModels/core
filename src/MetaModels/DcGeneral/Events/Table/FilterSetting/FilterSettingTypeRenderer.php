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
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Handles rendering of model from tl_metamodel_filtersetting.
 */
abstract class FilterSettingTypeRenderer
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     */
    private $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        $this->getServiceContainer()->getEventDispatcher()->addListener(
            ModelToLabelEvent::NAME,
            array($this, 'modelToLabel')
        );
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param ModelInterface $model The model for which to retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel(ModelInterface $model)
    {
        // NOTE: It is maybe not that wise to instantiate the whole filter setting here?
        $filterSetting = $this->getServiceContainer()->getFilterFactory()->createCollection($model->getProperty('fid'));

        return $filterSetting->getMetaModel();
    }

    /**
     * Retrieve the types this renderer is valid for.
     *
     * @return array
     */
    abstract protected function getTypes();

    /**
     * Retrieve the comment for the label.
     *
     * @param ModelInterface      $model      The filter setting to render.
     *
     * @param TranslatorInterface $translator The translator in use.
     *
     * @return string
     */
    protected function getLabelComment(ModelInterface $model, TranslatorInterface $translator)
    {
        if ($model->getProperty('comment')) {
            return sprintf(
                $translator->translate('typedesc._comment_', 'tl_metamodel_filtersetting'),
                specialchars($model->getProperty('comment'))
            );
        }
        return '';
    }

    /**
     * Retrieve the image for the label.
     *
     * @param ModelInterface $model The filter setting to render.
     *
     * @return string
     */
    protected function getLabelImage(ModelInterface $model)
    {
        $typeFactory = $this
            ->getServiceContainer()
            ->getFilterFactory()
            ->getTypeFactory($model->getProperty('type'));

        $image = $typeFactory ? $typeFactory->getTypeIcon() : null;
        if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
            $image = 'system/modules/metamodels/assets/images/icons/filter_default.png';
        }

        if (!$model->getProperty('enabled')) {
            $intPos = strrpos($image, '.');
            if ($intPos !== false) {
                $image = substr_replace($image, '_1', $intPos, 0);
            }
        }
        $dispatcher = $this->getServiceContainer()->getEventDispatcher();

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
     * @param TranslatorInterface $translator The translator in use.
     *
     * @param ModelInterface      $model      The filter setting to render.
     *
     * @return mixed|string
     */
    protected function getLabelText(TranslatorInterface $translator, ModelInterface $model)
    {
        $type  = $model->getProperty('type');
        $label = $translator->translate('typenames.' . $type, 'tl_metamodel_filtersetting');
        if ($label == 'typenames.' . $type) {
            return $type;
        }
        return $label;
    }

    /**
     * Retrieve the label pattern.
     *
     * @param EnvironmentInterface $environment The translator in use.
     *
     * @param ModelInterface       $model       The filter setting to render.
     *
     * @return string
     */
    protected function getLabelPattern(EnvironmentInterface $environment, ModelInterface $model)
    {
        $translator = $environment->getTranslator();
        $type       = $model->getProperty('type');
        $combined   = 'typedesc.' . $type;

        if (($resultPattern = $translator->translate($combined, 'tl_metamodel_filtersetting')) == $combined) {
            $resultPattern = $translator->translate('typedesc._default_', 'tl_metamodel_filtersetting');
        }

        return $resultPattern;
    }

    /**
     * Retrieve the parameters for the label with attribute name and url parameter.
     *
     * @param EnvironmentInterface $environment The translator in use.
     *
     * @param ModelInterface       $model       The model.
     *
     * @return array
     */
    protected function getLabelParametersWithAttributeAndUrlParam(
        EnvironmentInterface $environment,
        ModelInterface $model
    ) {
        $translator = $environment->getTranslator();
        $metamodel  = $this->getMetaModel($model);
        $attribute  = $metamodel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $attributeName = $attribute->getColName();
        } else {
            $attributeName = $model->getProperty('attr_id');
        }

        return array(
                $this->getLabelImage($model),
                $this->getLabelText($translator, $model),
                $this->getLabelComment($model, $translator),
                $attributeName,
                ($model->getProperty('urlparam') ? $model->getProperty('urlparam') : $attributeName)
            );
    }

    /**
     * Retrieve the parameters for the label with attribute name and url parameter.
     *
     * @param EnvironmentInterface $environment The translator in use.
     *
     * @param ModelInterface       $model       The model.
     *
     * @return array
     */
    protected function getLabelParametersNormal(EnvironmentInterface $environment, ModelInterface $model)
    {
        $translator = $environment->getTranslator();

        return array(
            $this->getLabelImage($model),
            $this->getLabelText($translator, $model),
            $this->getLabelComment($model, $translator),
            $model->getProperty('type')
        );
    }

    /**
     * Retrieve the parameters for the label.
     *
     * @param EnvironmentInterface $environment The translator in use.
     *
     * @param ModelInterface       $model       The model.
     *
     * @return array
     */
    protected function getLabelParameters(EnvironmentInterface $environment, ModelInterface $model)
    {
        return $this->getLabelParametersNormal($environment, $model);
    }

    /**
     * Render a filter setting into html.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName()
                !== 'tl_metamodel_filtersetting')
            || in_array($event->getModel()->getProperty('type'), $this->getTypes())
        ) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();

        $event
            ->setLabel($this->getLabelPattern($environment, $model))
            ->setArgs($this->getLabelParameters($environment, $model));
    }
}
