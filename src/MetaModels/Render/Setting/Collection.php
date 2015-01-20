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
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Render\Setting;

/**
 * Base implementation for render settings.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Collection implements ICollection
{
    /**
     * The base information for this render settings object.
     *
     * @var array
     */
    protected $arrBase = array();

    /**
     * The sub settings for all attributes.
     *
     * @var array
     */
    protected $arrSettings = array();

    /**
     * The jump to information buffered in this setting.
     *
     * @var array
     */
    protected $arrJumpTo;

    /**
     * Create a new instance.
     *
     * @param array $arrInformation The array that holds all base information for the new instance.
     */
    public function __construct($arrInformation = array())
    {
        foreach ($arrInformation as $strKey => $varValue) {
            $this->set($strKey, deserialize($varValue));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($strName)
    {
        return $this->arrBase[$strName];
    }

    /**
     * {@inheritdoc}
     */
    public function set($strName, $varSetting)
    {
        $this->arrBase[$strName] = $varSetting;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetting($strAttributeName)
    {
        return isset($this->arrSettings[$strAttributeName]) ? $this->arrSettings[$strAttributeName] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setSetting($strAttributeName, $objSetting)
    {
        if ($objSetting) {
            $this->arrSettings[$strAttributeName] = $objSetting->setParent($this);
        } else {
            unset($this->arrSettings[$strAttributeName]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingNames()
    {
        return array_keys($this->arrSettings);
    }

    /**
     * {@inheritdoc}
     */
    public function getJumpTo()
    {
        return isset($this->arrJumpTo) ? $this->arrJumpTo : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setJumpTo($varSetting)
    {
        $this->arrJumpTo = $varSetting;

        return $this;
    }
}
