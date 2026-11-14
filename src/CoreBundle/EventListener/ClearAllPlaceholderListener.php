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

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener;

use MetaModels\FrontendIntegration\FrontendFilter;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Replaces the "clear all" filter placeholders in pages rendered by modern (Twig based) layouts.
 *
 * The legacy "outputFrontendTemplate" hook is never called for those layouts, so the placeholder would be output as is.
 */
final class ClearAllPlaceholderListener
{
    private const PLACEHOLDER = '[[[metamodelfrontendfilterclearall::';

    public function __construct(private readonly FrontendFilter $frontendFilter)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $content  = $response->getContent();

        if (!\is_string($content) || !\str_contains($content, self::PLACEHOLDER)) {
            return;
        }

        $response->setContent($this->frontendFilter->replaceClearAllPlaceholders($content));
    }
}
