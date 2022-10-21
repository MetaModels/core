<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration\InputScreen;

/**
 * Implementation of IInputScreenGroupingAndSorting.
 */
class InputScreenGroupingAndSorting implements IInputScreenGroupingAndSorting
{
    /**
     * The parenting input screen.
     *
     * @var IInputScreen
     */
    protected $inputScreen;

    /**
     * The data for the input screen grouping and sorting.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new instance.
     *
     * @param array        $data        The information about the input screen.
     *
     * @param IInputScreen $inputScreen The information about all contained properties.
     */
    public function __construct($data, IInputScreen $inputScreen)
    {
        $this->data        = $data;
        $this->inputScreen = $inputScreen;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaModel()
    {
        return $this->inputScreen->getMetaModel();
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderGroupType()
    {
        if ($this->isManualSorting()) {
            return 'none';
        }

        return $this->data['rendergrouptype'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderGroupLength()
    {
        return (int) $this->data['rendergrouplen'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderGroupAttribute()
    {
        if (!empty($this->data['rendergroupattr'])) {
            $metaModel = $this->getMetaModel();
            if ($metaModel) {
                $attribute = $metaModel->getAttributeById((int) $this->data['rendergroupattr']);
                if ($attribute) {
                    return $attribute->getColName();
                }
            }
        }

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSortDirection()
    {
        return $this->data['rendersort'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSortAttribute()
    {
        if (!empty($this->data['rendersortattr'])) {
            $metaModel = $this->getMetaModel();
            if ($metaModel) {
                $attribute = $metaModel->getAttributeById((int) $this->data['rendersortattr']);
                if ($attribute) {
                    return $attribute->getColName();
                }
            }
        }

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function isManualSorting()
    {
        return (bool) $this->data['ismanualsort'];
    }

    /**
     * {@inheritDoc}
     */
    public function isDefault()
    {
        return (bool) $this->data['isdefault'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->data['name'];
    }
}
