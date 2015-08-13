<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;

/**
 * Data container definition when dealing in MetaModels context.
 *
 * It only provides convenience methods to access the IMetaModelDefinition.
 */
interface IMetaModelDataDefinition extends ContainerInterface
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
