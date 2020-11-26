<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

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
