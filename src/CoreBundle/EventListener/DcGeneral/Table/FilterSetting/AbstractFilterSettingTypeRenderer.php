<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles rendering of model from tl_metamodel_filtersetting.
 */
abstract class AbstractFilterSettingTypeRenderer
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeMatcher;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory    $filterSettingFactory The filter factory.
     * @param EventDispatcherInterface $dispatcher           The event dispatcher.
     * @param IconBuilder              $iconBuilder          The icon builder.
     * @param RequestScopeDeterminator $scopeMatcher         Request scope determinator.
     */
    public function __construct(
        IFilterSettingFactory $filterSettingFactory,
        EventDispatcherInterface $dispatcher,
        IconBuilder $iconBuilder,
        RequestScopeDeterminator $scopeMatcher
    ) {
        $this->factory     = $filterSettingFactory;
        $this->dispatcher  = $dispatcher;
        $this->iconBuilder = $iconBuilder;

        $this->scopeMatcher = $scopeMatcher;
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
        if (!$this->scopeMatcher->currentScopeIsBackend()) {
            return;
        }

        $model = $event->getModel();
        if (($model->getProviderName() !== 'tl_metamodel_filtersetting')
            || !in_array($event->getModel()->getProperty('type'), $this->getTypes())
        ) {
            return;
        }

        $environment = $event->getEnvironment();

        $event
            ->setLabel($this->getLabelPattern($environment, $model))
            ->setArgs($this->getLabelParameters($environment, $model));
    }

    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param ModelInterface $model The model for which to retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    protected function getMetaModel(ModelInterface $model)
    {
        // NOTE: It is maybe not that wise to instantiate the whole filter setting here?
        $filterSetting = $this->factory->createCollection($model->getProperty('fid'));

        return $filterSetting->getMetaModel();
    }

    /**
     * Retrieve the types this renderer is valid for.
     *
     * @return string[]
     */
    abstract protected function getTypes();

    /**
     * Retrieve the comment for the label.
     *
     * @param ModelInterface      $model      The filter setting to render.
     * @param TranslatorInterface $translator The translator in use.
     *
     * @return string
     */
    protected function getLabelComment(ModelInterface $model, TranslatorInterface $translator)
    {
        if ($model->getProperty('comment')) {
            return sprintf(
                $translator->translate('typedesc._comment_', 'tl_metamodel_filtersetting'),
                StringUtil::specialchars($model->getProperty('comment'))
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
        $typeFactory = $this->factory->getTypeFactory($model->getProperty('type'));

        $image = $this->iconBuilder->getBackendIconImageTag(
            $this->updateImageWithDisabled($model, $typeFactory->getTypeIcon()),
            '',
            '',
            $this->updateImageWithDisabled($model, 'bundles/metamodelscore/images/icons/filter_default.png')
        );

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $this->dispatcher->dispatch(
            new AddToUrlEvent('act=edit&amp;id=' . $model->getId()),
            ContaoEvents::BACKEND_ADD_TO_URL
        );

        return sprintf(
            '<a href="%s">%s</a>',
            $urlEvent->getUrl(),
            $image
        );
    }

    /**
     * Retrieve the label text for a filter setting.
     *
     * @param TranslatorInterface $translator The translator in use.
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

        return [
            $this->getLabelImage($model),
            $this->getLabelText($translator, $model),
            \sprintf(
                $translator->translate('typedesc._attribute_', 'tl_metamodel_filtersetting'),
                $attributeName,
                ($attribute ? $attribute->getName() : '')
            ),
            $this->getLabelComment($model, $translator),
            \sprintf(
                $translator->translate('typedesc._url_', 'tl_metamodel_filtersetting'),
                ($model->getProperty('urlparam') ?? $attributeName)
            )
        ];
    }

    /**
     * Retrieve the parameters for the label with attribute name and url parameter.
     *
     * @param EnvironmentInterface $environment The translator in use.
     * @param ModelInterface       $model       The model.
     *
     * @return array
     */
    protected function getLabelParametersNormal(EnvironmentInterface $environment, ModelInterface $model)
    {
        $translator = $environment->getTranslator();

        return [
            $this->getLabelImage($model),
            $this->getLabelText($translator, $model),
            '',
            $this->getLabelComment($model, $translator),
            ''
        ];
    }

    /**
     * Retrieve the parameters for the label.
     *
     * @param EnvironmentInterface $environment The translator in use.
     * @param ModelInterface       $model       The model.
     *
     * @return array
     */
    protected function getLabelParameters(EnvironmentInterface $environment, ModelInterface $model)
    {
        return $this->getLabelParametersWithAttributeAndUrlParam($environment, $model);
    }

    /**
     * Add the '_1' suffix to the image if it is disabled.
     *
     * @param ModelInterface $model The model.
     * @param string         $image The image to alter.
     *
     * @return mixed
     */
    private function updateImageWithDisabled(ModelInterface $model, $image)
    {
        $this->preCreateInverseImage($model, $image);

        if ($model->getProperty('enabled')) {
            return $image;
        }
        if (false === $intPos = strrpos($image, '.')) {
            return $image;
        }

        return substr_replace($image, '_1', $intPos, 0);
    }

    /**
     * Pre create the inverse image.
     *
     * @param ModelInterface $model The model.
     * @param string         $image The image for pre create.
     *
     * @return void
     */
    private function preCreateInverseImage(ModelInterface $model, string $image): void
    {
        if (false === $intPos = strrpos($image, '.')) {
            return;
        }

        if ($model->getProperty('enabled')) {
            $this->iconBuilder->getBackendIcon(substr_replace($image, '_1', $intPos, 0));

            return;
        }

        $this->iconBuilder->getBackendIcon($image);
    }
}
