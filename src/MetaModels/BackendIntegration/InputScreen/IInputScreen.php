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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use MetaModels\IMetaModel;

/**
 * This interface describes the abstraction of an input screen.
 */
interface IInputScreen
{
    /**
     * Retrieve the id of the input screen.
     *
     * @return int
     */
    public function getId();

    /**
     * Retrieve all legends.
     *
     * @return string[]
     */
    public function getLegends();

    /**
     * Retrieve the names of all legends.
     *
     * @return string[]
     */
    public function getLegendNames();

    /**
     * Retrieve a single legend information.
     *
     * @param string $name The name of the legend.
     *
     * @return array
     */
    public function getLegend($name);

    /**
     * Retrieve the property information.
     *
     * @return array
     */
    public function getProperties();

    /**
     * Retrieve a single property information.
     *
     * @param string $name The name of the property.
     *
     * @return array
     */
    public function getProperty($name);

    /**
     * Retrieve the names of all contained properties.
     *
     * @return string[]
     */
    public function getPropertyNames();

    /**
     * Retrieve the conditions for the given property name.
     *
     * @param string $name The name of the property.
     *
     * @return ConditionChainInterface
     */
    public function getConditionsFor($name);

    /**
     * Retrieve the conditions for the given property name.
     *
     * @return IInputScreenGroupingAndSorting[]
     */
    public function getGroupingAndSorting();

    /**
     * Get the MetaModel the input screen belongs to.
     *
     * @return IMetaModel
     */
    public function getMetaModel();

    /**
     * Retrieve the icon to be used in the backend.
     *
     * @return string
     */
    public function getIcon();

    /**
     * Retrieve the name of the backend section the input screen shall be added in.
     *
     * @return string
     */
    public function getBackendSection();

    /**
     * Retrieve the caption text to be used in the backend.
     *
     * @return array
     */
    public function getBackendCaption();

    /**
     * Retrieve the name of the parent table (only valid when not stand-alone mode).
     *
     * @return string|null
     */
    public function getParentTable();

    /**
     * Check if the input screen shall be injected as standalone module.
     *
     * @return bool
     */
    public function isStandalone();

    /**
     * Retrieve the render mode.
     *
     * @return string
     */
    public function getRenderMode();

    /**
     * Check if the render mode is hierarchical.
     *
     * @return bool
     */
    public function isHierarchical();

    /**
     * Check if the render mode is parent mode.
     *
     * @return bool
     */
    public function isParented();

    /**
     * Check if the render mode is flat mode.
     *
     * @return bool
     */
    public function isFlat();

    /**
     * Check if the MetaModel is closed.
     *
     * @return bool
     *
     * @deprecated use isEditable() and isCreatable() and isDeletable().
     */
    public function isClosed();

    /**
     * Check if the MetaModel is editable.
     *
     * @return bool
     */
    public function isEditable();

    /**
     * Check if the MetaModel is creatable.
     *
     * @return bool
     */
    public function isCreatable();

    /**
     * Check if the MetaModel is deletable.
     *
     * @return bool
     */
    public function isDeletable();

    /**
     * Get a string with the panel layout.
     *
     * @return string
     */
    public function getPanelLayout();
}
