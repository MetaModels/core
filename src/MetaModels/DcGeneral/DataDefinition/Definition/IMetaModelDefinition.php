<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;

/**
 * This interface describes a data definition of a MetaModel.
 */
interface IMetaModelDefinition extends DefinitionInterface
{
    /**
     * The name of the definition.
     */
    const NAME = 'metamodels';

    /**
     * Set the id of the active render setting for the MetaModel.
     *
     * @param string $renderSettingId The id.
     *
     * @return IMetaModelDefinition
     */
    public function setActiveRenderSetting($renderSettingId);

    /**
     * Check if there has been an active render setting defined.
     *
     * @return bool
     */
    public function hasActiveRenderSetting();

    /**
     * Retrieve the id of the active render setting.
     *
     * @return string
     */
    public function getActiveRenderSetting();

    /**
     * Set the active input screen for the MetaModel.
     *
     * @param string $renderSettingId The id.
     *
     * @return IMetaModelDefinition
     */
    public function setActiveInputScreen($renderSettingId);

    /**
     * Check if there has an active input screen defined.
     *
     * @return bool
     */
    public function hasActiveInputScreen();

    /**
     * Retrieve the id of the active input screen.
     *
     * @return string
     */
    public function getActiveInputScreen();
}
