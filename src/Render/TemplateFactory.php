<?php

declare(strict_types=1);

namespace MetaModels\Render;

use Contao\CoreBundle\Framework\Adapter;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
final readonly class TemplateFactory
{
    public function __construct(
        private Adapter $templateLoader,
        private RequestScopeDeterminator $requestScopeDeterminator,
        private TwigTemplateSurrogate $twigSurrogate,
    ) {
    }

    /**
     * Create a template instance.
     *
     * @param string $templateName The legacy template name (e.g. "mm_attr_text").
     * @param string $twigGroup    The Twig group ("attribute", "filter" or "item") enabling the Twig surrogate
     *                             "@Contao/metamodels/&lt;group&gt;/&lt;leaf&gt;.html.twig". Empty disables it.
     */
    public function createTemplate(string $templateName, string $twigGroup = ''): Template
    {
        return new Template(
            $templateName,
            $this->templateLoader,
            $this->requestScopeDeterminator,
            $this->twigSurrogate,
            $twigGroup,
        );
    }
}
