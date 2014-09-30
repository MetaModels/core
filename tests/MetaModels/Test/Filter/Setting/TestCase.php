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

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Filter\Setting\ICollection;
use MetaModels\IMetaModel;

abstract class TestCase extends \MetaModels\Test\TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($tableName = 'mm_unittest')
    {
        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array(),
            array(array())
        );

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue($tableName));

        return $metaModel;
    }

    /**
     * Mock an ICollection.
     *
     * @return ICollection
     */
    protected function mockFilterSetting($tableName = 'mm_unittest')
    {
        $filterSetting = $this->getMock(
            'MetaModels\Filter\Setting\Collection',
            array('getMetaModel'),
            array(array())
        );

        $filterSetting
            ->expects($this->any())
            ->method('getMetaModel')
            ->will($this->returnValue($this->mockMetaModel($tableName)));

        return $filterSetting;
    }
}
