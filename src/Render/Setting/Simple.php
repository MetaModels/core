<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render\Setting;

use Contao\StringUtil;

/**
 * Base implementation for render settings.
 */
class Simple implements ISimple
{
    /**
     * The base information for this render settings object.
     *
     * @var array
     */
    protected $arrBase = array();

    /**
     * The parenting instance.
     *
     * @var ICollection
     */
    protected $parent;

    /**
     * Create a new instance.
     *
     * @param array $arrInformation The array that holds all base information for the new instance.
     */
    public function __construct($arrInformation = array())
    {
        foreach ($arrInformation as $strKey => $varValue) {
            $this->set($strKey, StringUtil::deserialize($varValue));
        }
    }

    /**
     * Set the parenting render setting.
     *
     * @param ICollection $parent The parenting instance.
     *
     * @return ISimple
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Retrieve the parenting render setting.
     *
     * @return ICollection
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Retrieve a setting from the settings instance.
     *
     * @param string $strName The name of the setting to retrieve.
     *
     * @return mixed|null The value or null if not set.
     */
    public function get($strName)
    {
        return isset($this->arrBase[$strName]) ? $this->arrBase[$strName] : null;
    }

    /**
     * Set a base property in the settings object.
     *
     * @param string $strName    The name of the setting to set.
     *
     * @param mixed  $varSetting The value to use.
     *
     * @return ISimple The setting itself.
     */
    public function set($strName, $varSetting)
    {
        $this->arrBase[$strName] = $varSetting;
        return $this;
    }

    /**
     * Retrieve the names of all keys in this setting.
     *
     * @return string[]
     */
    public function getKeys()
    {
        return array_keys($this->arrBase);
    }
}
