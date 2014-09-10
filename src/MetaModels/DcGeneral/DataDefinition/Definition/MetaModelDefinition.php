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

/**
 * Default implementation of IMetaModelDefinition.
 *
 * @package MetaModels\DcGeneral\DataDefinition\Definition
 */
class MetaModelDefinition implements IMetaModelDefinition
{
    /**
     * The id of the active render setting.
     *
     * @var int
     */
    protected $activeRenderSetting;

    /**
     * The id of the active input screen.
     *
     * @var int
     */
    protected $activeInputScreen;

    /**
     * {@inheritdoc}
     */
    public function setActiveRenderSetting($id)
    {
        $this->activeRenderSetting = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveRenderSetting()
    {
        return isset($this->activeRenderSetting);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveRenderSetting()
    {
        return $this->activeRenderSetting;
    }

    /**
     * {@inheritdoc}
     */
    public function setActiveInputScreen($id)
    {
        $this->activeInputScreen = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveInputScreen()
    {
        return isset($this->activeInputScreen);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveInputScreen()
    {
        return $this->activeInputScreen;
    }
}
