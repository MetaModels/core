<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Helper;

use MetaModels\Helper\ToolboxFile;
use MetaModels\Test\TestCase;

/**
 * Test the attribute factory.
 *
 * @package MetaModels\Test\Filter\Setting
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
            'path'  => array()
        );

        $this->assertEquals($emptyExpected, ToolboxFile::convertUuidsOrPathsToMetaModels(null));
        $this->assertEquals($emptyExpected, ToolboxFile::convertUuidsOrPathsToMetaModels(array()));
        $this->assertEquals($emptyExpected, ToolboxFile::convertUuidsOrPathsToMetaModels(array(null)));
    }
}
