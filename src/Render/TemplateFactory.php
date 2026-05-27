<?php

declare(strict_types=1);

namespace MetaModels\Render;

use Contao\CoreBundle\Framework\Adapter;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;

final readonly class TemplateFactory
{
    public function __construct(
        private Adapter $templateLoader,
        private RequestScopeDeterminator $requestScopeDeterminator,
    ) {
    }

    public function createTemplate(string $templateName): Template
    {
        return new Template(
            $templateName,
            $this->templateLoader,
            $this->requestScopeDeterminator,
        );
    }
}
