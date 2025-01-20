<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class provides the template options.
 */
class EnvironmentOptionListener
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }
    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
            || ('use_only_in_env' !== $event->getPropertyName())
        ) {
            return;
        }

        $options = [
            'only_backend' => $this->translator->trans(
                'use_only_in_env.only_backend',
                [],
                'tl_metamodel_filtersetting'
            ),
            'only_frontend' => $this->translator->trans(
                'use_only_in_env.only_frontend',
                [],
                'tl_metamodel_filtersetting'
            ),
        ];

        $event->setOptions($options);
    }
}
