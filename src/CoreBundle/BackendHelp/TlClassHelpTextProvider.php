<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\BackendHelp;

use ContaoCommunityAlliance\DcGeneral\BackendHelp\BackendHelpProviderInterface;
use ContaoCommunityAlliance\DcGeneral\BackendHelp\HelpText;

use function array_key_exists;

final readonly class TlClassHelpTextProvider implements BackendHelpProviderInterface
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
        if ('tl_metamodel_dcasetting' === $table && 'tl_class' === $property) {
            foreach (
                [
                    'w25',
                    'w33',
                    'w50',
                    'w66',
                    'w75',
                    'clr',
                    'wizard',
                    'long',
                    'cbx',
                    'm12',
                    'cbx_m12',
                ] as $option
            ) {
                $helpText = new HelpText(
                    'tl_class_headline',
                    $option,
                    'tl_class_' . $option . '.caption',
                    'tl_class_' . $option . '.description',
                    'tl_metamodel_dcasetting',
                );
                if ($buffer[$helpText->getSection() . '_' . $helpText->getKey()] ?? false) {
                    continue;
                }
                yield $helpText;
            }
        }
    }
}
