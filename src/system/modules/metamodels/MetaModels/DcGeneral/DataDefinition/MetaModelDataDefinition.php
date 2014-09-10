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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;

/**
 * Default implementation of IMetaModelDataDefinition.
 *
 * @package MetaModels\DcGeneral\DataDefinition
 */
class MetaModelDataDefinition extends DefaultContainer implements IMetaModelDataDefinition
{
    /**
     * {@inheritDoc}
     */
    public function setMetaModelDefinition(IMetaModelDefinition $definition)
    {
        $this->setDefinition(IMetaModelDefinition::NAME, $definition);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetaModelDefinition()
    {
        return $this->hasDefinition(IMetaModelDefinition::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaModelDefinition()
    {
        return $this->getDefinition(IMetaModelDefinition::NAME);
    }
}
