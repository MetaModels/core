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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Rules;

use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Test\TestCase;

/**
 * Test greater-than filter rules.
 *
 * @covers \MetaModels\Filter\Rules\SearchAttribute
 */
class SearchAttributeTest extends TestCase
{
    /**
     * Test that the result equals the expected value.
     *
     * @return void
     */
    public function testSearchAttribute()
    {
        $attribute = $this->getMockForAbstractClass('MetaModels\\Attribute\\IAttribute');
        $attribute
            ->expects($this->once())
            ->method('searchFor')
            ->with('test')
            ->willReturn(array(1, 2, 3));

        $rule = new SearchAttribute($attribute, 'test');

        $this->assertEquals(array(1, 2, 3), $rule->getMatchingIds());
    }

    /**
     * Test that the result equals the expected value.
     *
     * @return void
     */
    public function testSearchTranslatedAttribute()
    {
        $attribute = $this->getMockForAbstractClass('MetaModels\\Attribute\\ITranslated');
        $attribute
            ->expects($this->once())
            ->method('searchForInLanguages')
            ->with('test')
            ->willReturn(array(1, 2, 3));

        $rule = new SearchAttribute($attribute, 'test');

        $this->assertEquals(array(1, 2, 3), $rule->getMatchingIds());
    }

    /**
     * Test that the result equals the expected value.
     *
     * @return void
     */
    public function testSearchTranslatedAttributeWithLanguageOverride()
    {
        $attribute = $this->getMockForAbstractClass('MetaModels\\Attribute\\ITranslated');
        $attribute
            ->expects($this->once())
            ->method('searchForInLanguages')
            ->with('test', array('de', 'en'))
            ->willReturn(array(1, 2, 3));

        $rule = new SearchAttribute($attribute, 'test', array('de', 'en'));

        $this->assertEquals(array(1, 2, 3), $rule->getMatchingIds());
    }
}
