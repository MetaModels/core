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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render\Setting;

use MetaModels\IItem;

/**
 * Interface for render settings.
 */
interface ICollection
{
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
     * @return ICollection The setting itself.
     */
    public function set($strName, $varSetting);

    /**
     * Get the render information for an attribute.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @return ISimple|null An object or null if the information is not available.
     */
    public function getSetting($strAttributeName);

    /**
     * Set the render information for an attribute.
     *
     * @param string       $strAttributeName The name of the attribute.
     *
     * @param ISimple|null $objSetting       The object containing all the information or null to clear the setting.
     *
     * @return ICollection The instance itself for chaining.
     */
    public function setSetting($strAttributeName, $objSetting);

    /**
     * Retrieve the names of all columns getting rendered via this setting.
     *
     * @return list<string>
     */
    public function getSettingNames();

    /**
     * Render a filter url for the given item.
     *
     * @param IItem $item          The item to generate the filter url for.
     * @param int   $referenceType Optional reference type - mandatory from MetaModels 3.0 on.
     *
     * @return array
     */
    public function buildJumpToUrlFor(IItem $item /**, int $referenceType */);
}
