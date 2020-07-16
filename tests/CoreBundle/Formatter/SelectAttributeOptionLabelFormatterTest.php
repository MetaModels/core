<?php

declare(strict_types=1);

namespace MetaModels\Test\CoreBundle\Formatter;

use MetaModels\Attribute\IAttribute;
use MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter;
use PHPUnit\Framework\TestCase;

/**
 * The test for MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter.
 *
 * @covers \MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter
 */
class SelectAttributeOptionLabelFormatterTest extends TestCase
{
    public function testFormatLabel(): void
    {
        $attribute = $this->getMockBuilder(IAttribute::class)->getMockForAbstractClass();
        $attribute
            ->expects(self::once())
            ->method('getName')
            ->willReturn('MyAttribute');
        $attribute
            ->expects(self::once())
            ->method('get')
            ->willReturn('type');
        $attribute
            ->expects(self::once())
            ->method('getColName')
            ->willReturn('colName');

        $formatter = new SelectAttributeOptionLabelFormatter();
        self::assertSame('MyAttribute [type, "colName"]', $formatter->formatLabel($attribute));
    }
}
