<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class DoctrineSchemaProvider
{
    public function __construct(
        private ?int $priority = null,
    ) {
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }
}
