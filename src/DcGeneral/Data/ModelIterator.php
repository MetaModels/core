<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

/**
 * Iterator class for allowing usage of MetaModels\DcGeneral\Data\Model
 * in foreach constructs.
 */
class ModelIterator implements \Iterator
{
    /**
     * The model to iterate over.
     *
     * @var Model
     */
    protected $objModel = null;

    /**
     * The current position of the index.
     *
     * @var int
     */
    private $intPosition = 0;

    /**
     * All property names.
     *
     * @var string[]
     */
    protected $arrKeys = array();

    /**
     * Returns an array containing all property names.
     *
     * @return string[] all property names.
     */
    protected function getKeys()
    {
        return $this->arrKeys;
    }

    /**
     * Create a new instance for the given model.
     *
     * @param Model $objModel The model to iterate over.
     */
    public function __construct(Model $objModel)
    {
        $this->intPosition = 0;
        $this->objModel    = $objModel;
        $objMetaModel      = $this->objModel->getItem()->getMetaModel();

        $arrKeys = array();
        if ($objMetaModel->hasVariants()) {
            $arrKeys[] = 'varbase';
            $arrKeys[] = 'vargroup';
        }
        $this->arrKeys = array_merge($arrKeys, array_keys($objMetaModel->getAttributes()));
    }

    /**
     * Reset the iterator to the beginning.
     *
     * @return void
     */
    public function rewind()
    {
        $this->intPosition = 0;
    }

    /**
     * Return the current value.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->objModel->getProperty($this->key());
    }

    /**
     * Return the current property name.
     *
     * @return string
     */
    public function key()
    {
        $arrKeys = $this->getKeys();
        return $arrKeys[$this->intPosition];
    }

    /**
     * Move the iterator one step forward.
     *
     * @return void
     */
    public function next()
    {
        ++$this->intPosition;
    }

    /**
     * Determine if the current index is still valid.
     *
     * @return bool
     */
    public function valid()
    {
        return strlen($this->key()) > 0;
    }
}
