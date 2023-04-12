<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BackendViewInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IFactory;

/**
 * Event handler class to manage the "create variant" button.
 */
class CreateVariantButtonListener
{
    /**
     * The factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeMatcher;

    /**
     * Create a new instance.
     *
     * @param IFactory                 $factory      The factory.
     *
     * @param RequestScopeDeterminator $scopeMatcher The request scope determinator.
     */
    public function __construct(IFactory $factory, RequestScopeDeterminator $scopeMatcher)
    {
        $this->factory      = $factory;
        $this->scopeMatcher = $scopeMatcher;
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
        if ('createvariant' !== $event->getCommand()->getName()) {
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
        if (false === $this->scopeMatcher->currentScopeIsBackend()
            || 'createvariant' !== $event->getAction()->getName()) {
            return;
        }

        $environment   = $event->getEnvironment();
        $view          = $environment->getView();
        $dataProvider  = $environment->getDataProvider();
        $inputProvider = $environment->getInputProvider();
        $modelId       = $inputProvider->hasParameter('id')
            ? ModelId::fromSerialized($inputProvider->getParameter('id'))
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

        $metaModel = $this->factory->getMetaModel($model->getProviderName());

        if (!$metaModel || !$metaModel->hasVariants()) {
            return;
        }

        $preFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PreCreateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch($copyEvent, $copyEvent::NAME);
        };

        $postFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PostCreateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch($copyEvent, $copyEvent::NAME);
        };

        if (!$view instanceof BackendViewInterface) {
            throw new \InvalidArgumentException('Invalid view registered in environment.');
        }
        $editMask = new EditMask($view, $model, null, $preFunction, $postFunction, $this->breadcrumb($environment));
        $event->setResponse($editMask->execute());
    }

    /**
     * Check the items before the edit start. If there is a item with variant support and a empty vargroup it must be a
     * base. So set the varbase to 1.
     *
     * @param PreEditModelEvent $event The event with the model.
     *
     * @return void
     */
    public function presetVariantBase(PreEditModelEvent $event)
    {
        $model = $event->getModel();

        // Check of we have the driver from MetaModels. Only these request are from interest.
        if (!$model instanceof Model) {
            return;
        }

        // Get the item and check the context.
        $nativeItem = $model->getItem();
        $metaModel  = $nativeItem->getMetaModel();

        if ($metaModel->hasVariants() && (!$nativeItem->get('vargroup'))) {
            $nativeItem->set('varbase', '1');
        }
    }

    /**
     * Get the breadcrumb navigation via event.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function breadcrumb(EnvironmentInterface $environment)
    {
        $event = new GetBreadcrumbEvent($environment);

        $environment->getEventDispatcher()->dispatch($event, $event::NAME);

        $arrReturn = $event->getElements();

        if (!is_array($arrReturn) || count($arrReturn) == 0) {
            return null;
        }

        $GLOBALS['TL_CSS'][] = 'bundles/ccadcgeneral/css/generalBreadcrumb.css';

        $objTemplate           = new ContaoBackendViewTemplate('dcbe_general_breadcrumb');
        $objTemplate->elements = $arrReturn;

        return $objTemplate->parse();
    }
}
