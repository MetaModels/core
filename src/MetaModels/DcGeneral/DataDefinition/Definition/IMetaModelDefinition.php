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

namespace MetaModels\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;

/**
 * This interface describes a data definition of a MetaModel.
 *
 * @package MetaModels\DcGeneral\DataDefinition\Definition
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
