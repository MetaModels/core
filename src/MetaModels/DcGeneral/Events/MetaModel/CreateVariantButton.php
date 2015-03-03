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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BackendViewInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreCreateModelEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\MetaModelsServiceContainer;

/**
 * Event handler class to manage the "create variant" button.
 */
class CreateVariantButton extends BaseSubscriber
{
    /**
     * Register all listeners.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this->addListener(
            GetOperationButtonEvent::NAME,
            array($this, 'createButton')
            )
            ->addListener(
                DcGeneralEvents::ACTION,
                array($this, 'handleCreateVariantAction')
            );
    }

    /**
     * Check if we have to add the "Create variant" button.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function createButton(GetOperationButtonEvent $event)
    {
        if($event->getCommand()->getName() != 'createvariant') {
            return;
        }
        /** @var Model $model */
        $model     = $event->getModel();
        $metamodel = $model->getItem()->getMetaModel();

        if (!$metamodel->hasVariants() || $model->getProperty('varbase') === '0') {
            $event->setHtml('');
        }
    }

    /**
     * Handle the "create variant" event.
     *
     * @param ActionEvent $event The action Event being executed.
     *
     * @return void
     *
     * @throws \RuntimeException When the base model can not be found.
     * @throws \InvalidArgumentException When the view in the environment is incompatible.
     */
    public function handleCreateVariantAction(ActionEvent $event)
    {
        $environment   = $event->getEnvironment();

        if($event->getAction()->getName() != 'createvariant') {
            return;
        }

        $view          = $environment->getView();
        $dataProvider  = $environment->getDataProvider();
        $inputProvider = $environment->getInputProvider();
        $modelId       = $inputProvider->hasParameter('id')
            ? IdSerializer::fromSerialized($inputProvider->getParameter('id'))
            : null;

        /** @var \MetaModels\DcGeneral\Data\Driver $dataProvider */
        $model = $dataProvider
            ->createVariant(
                $dataProvider
                    ->getEmptyConfig()
                    ->setId($modelId->getId())
            );

        if ($model == null) {
            throw new \RuntimeException(sprintf(
                'Could not find model with id %s for creating a variant.',
                $modelId
            ));
        }

        $metaModel = $this
            ->getServiceContainer()
            ->getFactory()
            ->getMetaModel($model->getProviderName());

        if(!$metaModel || !$metaModel->hasVariants()) {
            return;
        }

        $preFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PreCreateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        $postFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PostCreateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        if (!$view instanceof BackendViewInterface) {
            throw new \InvalidArgumentException('Invalid view registered in environment.');
        }
        $editMask = new EditMask($view, $model, null, $preFunction, $postFunction);
        $event->setResponse($editMask->execute());
    }
}
