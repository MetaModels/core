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

namespace MetaModels\Render\Setting;

/**
 * Interface for render settings.
 */
interface ISimple
{
    /**
     * Set the parenting render setting.
     *
     * @param ICollection $parent The parenting instance.
     *
     * @return ISimple
     */
    public function setParent($parent);

    /**
     * Retrieve the parenting render setting.
     *
     * @return ICollection
     */
    public function getParent();

    /**
     * Retrieve a setting from the settings instance.
     *
     * @param string $strName The name of the setting to retrieve.
     *
     * @return mixed|null The value or null if not set.
     */
    public function get($strName);

    /**
     * Set a base property in the settings object.
     *
     * @param string $strName    The name of the setting to set.
     *
     * @param mixed  $varSetting The value to use.
     *
     * @return ISimple The setting itself.
     */
    public function set($strName, $varSetting);

    /**
     * Retrieve the names of all keys in this setting.
     *
     * @return string[]
     */
    public function getKeys();
}
