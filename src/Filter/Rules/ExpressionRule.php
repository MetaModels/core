<?php

declare(strict_types=1);

namespace MetaModels\Filter\Rules;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\IFilterRule;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * This is the MetaModel filter interface.
 */
final readonly class ExpressionRule implements IFilterRule
{
    public function __construct(
        private string $expression,
        private array $parameters,
        private ExpressionLanguage $expressionLanguage,
        private ?IFilter $ifTrue,
        private ?IFilter $ifFalse,
    ) {
    }

    /** @return list<string>|null */
    #[\Override]
    public function getMatchingIds(): ?array
    {
        if ((bool) $this->expressionLanguage->evaluate($this->expression, $this->parameters)) {
            return $this->ifTrue?->getMatchingIds();
        }

        if (null !== $this->ifFalse) {
            return $this->ifFalse->getMatchingIds();
        }

        return [];
    }
}
