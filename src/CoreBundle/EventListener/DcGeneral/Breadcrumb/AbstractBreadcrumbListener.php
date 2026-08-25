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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use MetaModels\Events\GetBreadcrumbShortcutIconEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    private BreadcrumbStoreFactory $storeFactory;

    /**
     * The parent element renderer.
     *
     * @var AbstractBreadcrumbListener|null
     */
    private ?AbstractBreadcrumbListener $parent;

    private UrlGeneratorInterface $urlGenerator;

    /**
     * Create a new instance.
     *
     * @param BreadcrumbStoreFactory          $storeFactory The store factory.
     * @param AbstractBreadcrumbListener|null $parent       Optional parent renderer.
     * @param string                          $routePrefix  The $route prefix.
     */
    public function __construct(
        BreadcrumbStoreFactory $storeFactory,
        ?AbstractBreadcrumbListener $parent = null,
        ?UrlGeneratorInterface $urlGenerator = null,
    ) {
        $this->storeFactory = $storeFactory;
        $this->parent       = $parent;
        if (null === $urlGenerator) {
            $urlGenerator = System::getContainer()->get('router');
            assert($urlGenerator instanceof UrlGeneratorInterface);
            \trigger_deprecation('metamodels/core', '2.4.0', 'The "$urlGenerator" argument will become mandatory.');
        }
        $this->urlGenerator  = $urlGenerator;
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
        $this->addShortcuts($elements);
        $event->setElements($elements->getElements());
        $event->setShortcuts($elements->getShortcuts());
        $event->stopPropagation();
    }

    /**
     * Add links to the sibling views of the MetaModel the current view belongs to.
     *
     * Without them, switching from the filters of a MetaModel to its attributes means going back
     * out to the list of all MetaModels first and picking the right row again.
     *
     * Nothing is added in the list of all MetaModels itself: no single MetaModel is being worked
     * on there, so the id is absent and there is nothing to link to. This is also why the id is
     * read rather than the table name - it tells the two cases apart on its own.
     *
     * The entries are taken from the operations of tl_metamodel instead of a list of their own,
     * so that operations other bundles inject - the note list, for one - come along without this
     * needing to know about them.
     *
     * @param BreadcrumbStore $elements The elements generated so far.
     *
     * @return void
     */
    private function addShortcuts(BreadcrumbStore $elements)
    {
        $modelId = $elements->getId('tl_metamodel');
        if (null === $modelId || '' === $modelId) {
            return;
        }

        $serialized = ModelId::fromValues('tl_metamodel', $modelId)->getSerialized();

        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        foreach ($this->siblingOperations() as $name => $operation) {
            \parse_str(\str_replace('&amp;', '&', (string) $operation['href']), $parameters);

            // The icon of the operation is fixed, while an area may want to say something about
            // this one MetaModel - the note list fills its sheet where lists are configured.
            $iconEvent = new GetBreadcrumbShortcutIconEvent(
                (string) $name,
                $modelId,
                (string) ($operation['icon'] ?? '')
            );
            $dispatcher->dispatch($iconEvent, GetBreadcrumbShortcutIconEvent::NAME);

            $label = $operation['label'] ?? '';
            // Operation labels conventionally follow Contao's [label, title] array format (see e.g.
            // isotope-bridge's tl_metamodel.php operations.isotope) - only a plain string here would
            // have worked before, so unwrap the array instead of naively string-casting it.
            if (\is_array($label)) {
                $label = $label[0] ?? '';
            }

            $elements->pushShortcut(
                $this->generate('metamodels.configuration', ['pid' => $serialized] + $parameters),
                (string) $label,
                $iconEvent->getIcon()
            );
        }
    }

    /**
     * Retrieve the operations of tl_metamodel that lead to a view of their own.
     *
     * Operations acting on the record itself - edit, delete and the like - carry an "act" in
     * their href and are left out; the ones wanted here switch the table.
     *
     * @return array<string, array<string, mixed>>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function siblingOperations(): array
    {
        $this->framework()->getAdapter(Controller::class)->loadDataContainer('tl_metamodel');
        System::loadLanguageFile('tl_metamodel');

        /** @psalm-suppress MixedArrayAccess - $GLOBALS['TL_DCA'] is an untyped Contao superglobal. */
        $operations = $GLOBALS['TL_DCA']['tl_metamodel']['list']['operations'] ?? [];
        assert(\is_array($operations));

        $wanted = [];
        foreach ($operations as $name => $operation) {
            if (!\is_array($operation) || !isset($operation['href'], $operation['icon'])) {
                continue;
            }
            if (!\str_contains((string) $operation['href'], 'table=')) {
                continue;
            }

            $wanted[$name] = $operation;
        }

        return $wanted;
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
     * @param string               $parameterName The parameter name containing the id.
     *
     * @return string
     */
    protected function extractIdFrom(EnvironmentInterface $environment, $parameterName = 'pid')
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $parameter = $inputProvider->getParameter($parameterName);

        return (string) ModelId::fromSerialized($parameter)->getId();
    }

    protected function generate(string $route, array $parameters): string
    {
        // TODO: Add ref & rt from current URL?
        return $this->urlGenerator->generate($route, $parameters);
    }

    /**
     * Retrieve the Contao framework.
     *
     * Not a constructor argument: every breadcrumb listener is wired by hand in the service
     * configuration, and one more argument would have to be added to each of them for a
     * dependency only this method needs.
     *
     * @return ContaoFramework
     */
    private function framework(): ContaoFramework
    {
        $framework = System::getContainer()->get('contao.framework');
        assert($framework instanceof ContaoFramework);

        return $framework;
    }
}
