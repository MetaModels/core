<?php

namespace MetaModels\CoreBundle\Contao\Picker;

use ContaoCommunityAlliance\DcGeneral\Picker\IdTranscoderInterface;
use InvalidArgumentException;

use function preg_match;
use function strtr;

final readonly class InsertTagIdTranscoder implements IdTranscoderInterface
{
    public function __construct(
        private string $metaModel,
        private string $renderSettingId,
    ) {
    }

    public function encode(string $id): string
    {
        return strtr(
            '{{mm::jumpTo::<metaModel>::<id>::<rendersettingId>}}',
            [
                '<metaModel>'       => $this->metaModel,
                '<rendersettingId>' => $this->renderSettingId,
                '<id>'              => $id
            ]
        );
    }

    public function decode(string $encodedId): string
    {
        if (
            1 !== preg_match(
                '#^{{mm::jumpTo::(?<metaModel>[^:]*)::(?<id>[^:]*)::(?<rendersettingId>[^|}]*)(:?\|.*)?}}$#',
                $encodedId,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Unparsable id value');
        }
        if ($matches['metaModel'] !== $this->metaModel || $matches['rendersettingId'] !== $this->renderSettingId) {
            throw new InvalidArgumentException('Not my id value');
        }

        return $matches['id'];
    }
}
