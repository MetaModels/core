<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\BackendHelp;

use ContaoCommunityAlliance\DcGeneral\BackendHelp\BackendHelpProviderInterface;
use ContaoCommunityAlliance\DcGeneral\BackendHelp\HelpText;

use function array_key_exists;

final readonly class CustomSqlHelpTextProvider implements BackendHelpProviderInterface
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
        if ('tl_metamodel_filtersetting' === $table && 'customsql' === $property) {
            foreach (
                [
                    'abstract',
                    'example1',
                    'example2',
                    'inserttags',
                    'secure_inserttags',
                    'parameter_sources',
                    'example3',
                ] as $option
            ) {
                $helpText = new HelpText(
                    '',
                    $option,
                    'customsql_' . $option . '.caption',
                    'customsql_' . $option . '.description',
                    'tl_metamodel_filtersetting',
                );
                if ($buffer[$helpText->getSection() . '_' . $helpText->getKey()] ?? false) {
                    continue;
                }
                yield $helpText;
            }
        }
    }
}
