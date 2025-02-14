<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\BackendHelp;

use ContaoCommunityAlliance\DcGeneral\BackendHelp\BackendHelpProviderInterface;
use ContaoCommunityAlliance\DcGeneral\BackendHelp\HelpText;

use function array_key_exists;

final readonly class TypeHelpTextProvider implements BackendHelpProviderInterface
{
    public function __construct(
        private BackendHelpProviderInterface $previous,
    ) {
    }

    public function getHelpFor(string $table, string $property): iterable
    {
        $buffer = [];
        foreach ($this->previous->getHelpFor($table, $property) as $helpText) {
            $buffer[$helpText->getSection() . '_' . $helpText->getKey()] = true;
            yield $helpText;
        }
        if ('tl_metamodel_dcasetting_condition' === $table && 'type' === $property) {
            foreach (
                [
                    'value_is',
                    'values_contain',
                    'is_visible',
                    'or',
                    'and',
                    'not',
                ] as $option
            ) {
                $helpText = new HelpText(
                    '',
                    $option,
                    'panelLayout_' . $option . '.caption',
                    'panelLayout_' . $option . '.description',
                    'tl_metamodel_dcasetting_condition',
                );
                if ($buffer[$helpText->getSection() . '_' . $helpText->getKey()] ?? false) {
                    continue;
                }
                yield $helpText;
            }
        }
    }
}
