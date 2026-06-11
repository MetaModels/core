<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Filter\Setting\Collection;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    /**
     * When no MetaModel is set (e.g. FE list module with no filter configured),
     * getParameterFilterWidgets() must return an empty array instead of throwing a RuntimeException.
     */
    public function testGetParameterFilterWidgetsReturnsEmptyArrayWhenNoMetaModelSet(): void
    {
        $collection = new Collection([]);

        $result = $collection->getParameterFilterWidgets([], [], new FrontendFilterOptions());

        self::assertSame([], $result);
    }

    /**
     * getParameters() returns an empty array when the collection has no settings.
     */
    public function testGetParametersReturnsEmptyArrayWhenNoSettings(): void
    {
        $collection = new Collection([]);

        self::assertSame([], $collection->getParameters());
    }
}
