<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Helper;

use MetaModels\Helper\ToolboxFile;
use MetaModels\Test\TestCase;

/**
 * Test the attribute factory.
 */
class ToolBoxFileTest extends TestCase
{
    /**
     * Test all empty values are mapped correctly.
     *
     * See https://github.com/MetaModels/attribute_file/issues/45#issuecomment-85937268
     *
     * @return void
     */
    public function testConvertUuidsOrPathsToMetaModelsEmpty()
    {
        $emptyExpected = array(
            'bin'   => array(),
            'value' => array(),
            'path'  => array(),
            'meta'  => array()
        );

        $this->assertEquals($emptyExpected, ToolboxFile::convertUuidsOrPathsToMetaModels(null));
        $this->assertEquals($emptyExpected, ToolboxFile::convertUuidsOrPathsToMetaModels(array()));
        $this->assertEquals($emptyExpected, ToolboxFile::convertUuidsOrPathsToMetaModels(array(null)));
    }
}
