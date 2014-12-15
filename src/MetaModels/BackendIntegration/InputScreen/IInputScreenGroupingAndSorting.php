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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration\InputScreen;

use MetaModels\IMetaModel;

/**
 * This interface describes the abstraction of an input screen grouping and sorting information.
 */
interface IInputScreenGroupingAndSorting
{
    /**
     * Get the MetaModel the input screen belongs to.
     *
     * @return IMetaModel
     */
    public function getMetaModel();

    /**
     * Retrieve the grouping type.
     *
     * @return string
     */
    public function getRenderGroupType();

    /**
     * Retrieve the amount of chars to use for grouping.
     *
     * @return string
     */
    public function getRenderGroupLength();

    /**
     * Retrieve the name of the attribute to use for grouping.
     *
     * @return string
     */
    public function getRenderGroupAttribute();

    /**
     * Retrieve the sorting direction.
     *
     * @return string
     */
    public function getRenderSortDirection();

    /**
     * Retrieve the render sort attribute name.
     *
     * @return string
     */
    public function getRenderSortAttribute();

    /**
     * Determine if manual sorting is enabled or not.
     *
     * @return bool
     */
    public function isManualSorting();

    /**
     * Determine if this is the default sorting.
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Retrieve the name.
     *
     * @return string
     */
    public function getName();
}
