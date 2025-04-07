<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\BackendHelp;

use ContaoCommunityAlliance\DcGeneral\BackendHelp\BackendHelpProviderInterface;
use ContaoCommunityAlliance\DcGeneral\BackendHelp\HelpText;

use function array_key_exists;

final readonly class PanelLayoutHelpTextProvider implements BackendHelpProviderInterface
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
        if ('tl_metamodel_dca' === $table && 'panelLayout' === $property) {
            foreach (
                [
                    'filter',
                    'search',
                    'sort',
                    'limit',
                ] as $option
            ) {
                $helpText = new HelpText(
                    'panelLayout_headline',
                    $option,
                    'panelLayout_' . $option . '.caption',
                    'panelLayout_' . $option . '.description',
                    'tl_metamodel_dca',
                );
                if ($buffer[$helpText->getSection() . '_' . $helpText->getKey()] ?? false) {
                    continue;
                }
                yield $helpText;
            }
        }
    }
}
