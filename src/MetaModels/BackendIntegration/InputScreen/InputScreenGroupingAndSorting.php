<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
                $attribute = $metaModel->getAttributeById($this->data['rendergroupattr']);
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
                $attribute = $metaModel->getAttributeById($this->data['rendersortattr']);
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
