<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Definition;

/**
 * Default implementation of IMetaModelDefinition.
 *
 * @psalm-suppress MissingConstructor
 */
class MetaModelDefinition implements IMetaModelDefinition
{
    /**
     * The id of the active render setting.
     *
     * @var string
     */
    protected $activeRenderSetting;

    /**
     * The id of the active input screen.
     *
     * @var string
     */
    protected $activeInputScreen;

    /**
     * {@inheritdoc}
     */
    public function setActiveRenderSetting($renderSettingId)
    {
        $this->activeRenderSetting = $renderSettingId;

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
    public function setActiveInputScreen($renderSettingId)
    {
        $this->activeInputScreen = $renderSettingId;

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
