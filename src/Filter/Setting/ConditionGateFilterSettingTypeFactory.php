<?php

declare(strict_types=1);

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\Setting\Condition\ConditionGate;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Attribute type factory for GATE filter settings.
 */
class ConditionGateFilterSettingTypeFactory implements IFilterSettingTypeFactory
{
    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getTypeName(): string
    {
         return 'conditiongate';
    }

    public function getTypeIcon(): string
    {
        return 'bundles/metamodelscore/images/icons/filter_gate.png';
    }

    public function isNestedType(): bool
    {
        return true;
    }

    public function getMaxChildren(): int
    {
        return 2;
    }

    public function getKnownAttributeTypes(): ?array
    {
        return null;
    }

    public function addKnownAttributeType($typeName)
    {
        throw new \LogicException('Filter setting "' . ConditionGate::class . '" can not handle attributes.');
    }

    #[\Override]
    public function createInstance($information, $filterSettings): ?ISimple
    {
        return new ConditionGate(
            $information,
            $this->expressionLanguage,
            $this->requestStack,
            $filterSettings->getMetaModel(),
            $this->translator,
        );
    }
}
