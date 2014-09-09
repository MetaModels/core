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

namespace MetaModels\DcGeneral\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;

/**
 * Data container definition when dealing in MetaModels context.
 *
 * It only provides convenience methods to access the IMetaModelDefinition.
 *
 * @package MetaModels\DcGeneral\DataDefinition
 */
interface IMetaModelDataDefinition
    extends ContainerInterface
{
    /**
     * Set the MetaModel definition.
     *
     * @param IMetaModelDefinition $definition The definition.
     *
     * @return mixed
     */
    public function setMetaModelDefinition(IMetaModelDefinition $definition);

    /**
     * Check if a MetaModel definition has been set.
     *
     * @return bool
     */
    public function hasMetaModelDefinition();

    /**
     * Retrieve the MetaModel definition.
     *
     * @return IMetaModelDefinition
     */
    public function getMetaModelDefinition();
}
