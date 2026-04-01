<?php

declare(strict_types=1);

namespace MetaModels\Filter\Setting;

use Override;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Attribute type factory for expression filter settings.
 */
final readonly class ExpressionRuleFilterSettingTypeFactory implements IFilterSettingTypeFactory
{
    public function __construct(
        private ExpressionLanguage $expressionLanguage,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {
    }

    #[Override]
    public function getTypeName(): string
    {
         return 'expression_rule';
    }

    #[Override]
    public function getTypeIcon(): string
    {
        return 'bundles/metamodelscore/images/icons/filter_expression.png';
    }

    #[Override]
    public function isNestedType(): bool
    {
        return true;
    }

    #[Override]
    public function getMaxChildren(): int
    {
        return 2;
    }

    #[Override]
    public function getKnownAttributeTypes(): ?array
    {
        return null;
    }

    #[Override]
    public function addKnownAttributeType($typeName)
    {
        throw new \LogicException('Filter setting "' . ExpressionRule::class . '" can not handle attributes.');
    }

    #[Override]
    public function createInstance($information, $filterSettings): ?ISimple
    {
        return new ExpressionRule(
            $information,
            $this->expressionLanguage,
            $this->requestStack,
            $filterSettings->getMetaModel(),
            $this->translator,
        );
    }
}
