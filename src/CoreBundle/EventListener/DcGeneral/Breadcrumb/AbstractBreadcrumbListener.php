<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This class renders various breadcrumbs.
 */
abstract class AbstractBreadcrumbListener
{
    /**
     * The breadcrumb store factory.
     *
     * @var BreadcrumbStoreFactory
     */
    private $storeFactory;

    /**
     * The parent element renderer.
     *
     * @var AbstractBreadcrumbListener
     */
    private $parent;

    /**
     * Create a new instance.
     *
     * @param BreadcrumbStoreFactory          $storeFactory The store factory.
     * @param AbstractBreadcrumbListener|null $parent       Optional parent renderer.
     */
    public function __construct(
        BreadcrumbStoreFactory $storeFactory,
        AbstractBreadcrumbListener $parent = null
    ) {
        $this->storeFactory = $storeFactory;
        $this->parent       = $parent;
    }

    /**
     * Event handler.
     *
     * @param GetBreadcrumbEvent $event The event.
     *
     * @return void
     */
    public function getBreadcrumb(GetBreadcrumbEvent $event)
    {
        $environment = $event->getEnvironment();
        if (!$this->wantToHandle($event)) {
            return;
        }

        $elements = $this->storeFactory->createStore();
        $this->getBreadcrumbElements($environment, $elements);
        $event->setElements($elements->getElements());
        $event->stopPropagation();
    }

    /**
     * Test if we want to handle the event.
     *
     * @param GetBreadcrumbEvent $event The event.
     *
     * @return bool
     */
    abstract protected function wantToHandle(GetBreadcrumbEvent $event);

    /**
     * Perform the bread crumb generating.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param BreadcrumbStore      $elements    The elements generated so far.
     *
     * @return void
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        if ($this->parent) {
            $this->parent->getBreadcrumbElements($environment, $elements);
        }
    }

    /**
     * Extract the id value from the serialized parameter with the given name.
     *
     * @param EnvironmentInterface $environment   The environment.
     *
     * @param string               $parameterName The parameter name containing the id.
     *
     * @return int
     */
    protected function extractIdFrom(EnvironmentInterface $environment, $parameterName = 'pid')
    {
        $parameter = $environment->getInputProvider()->getParameter($parameterName);

        return ModelId::fromSerialized($parameter)->getId();
    }
}
