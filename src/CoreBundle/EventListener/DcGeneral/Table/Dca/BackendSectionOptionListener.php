<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Menu\BackendMenuBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_diff;
use function array_keys;

/**
 * This provides the backend section options.
 */
class BackendSectionOptionListener
{
    public function __construct(
        private BackendMenuBuilder $builder,
        private TranslatorInterface $translator,
        private ContaoFramework $framework,
    ) {
    }

    /**
     * Retrieve a list of all backend sections, like "content", "system" etc.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_dca' !== $dataDefinition->getName())
            || ('backendsection' !== $event->getPropertyName())
        ) {
            return;
        }

        $options = [];
        foreach ($this->getMenu()->getChildren() as $option) {
            $label = $option->getLabel();
            if (false !== $domain = $option->getExtra('translation_domain')) {
                $label = $this->translator->trans($label, $option->getExtra('translation_params') ?? [], $domain);
            }

            $options[$option->getName()] = $label;
        }

        $event->setOptions($options);
    }

    private function getMenu(): ItemInterface
    {
        // Work around legacy Contao code - Menu builder loads via global lang array instead of translator.
        /** @psalm-suppress InternalMethod - Class ContaoFramework is internal, not the getAdapter() method. */
        $contaoController = $this->framework->getAdapter(Controller::class);
        $contaoController->loadLanguageFile('modules');

        return $this->builder->buildMainMenu();
    }
}
