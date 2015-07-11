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

namespace MetaModels\Events;

use MetaModels\IItem;
use MetaModels\Render\Setting\ICollection;

/**
 * This event is triggered when a MetaModels item is parsed.
 */
class ParseItemEvent
{
    /**
     * The render setting.
     *
     * @var ICollection
     */
    protected $renderSettings;

    /**
     * The item.
     *
     * @var IItem
     */
    protected $item;

    /**
     * The desired format.
     *
     * @var string
     */
    protected $desiredFormat;

    /**
     * The parsed result.
     *
     * @var array
     */
    protected $result;

    /**
     * Create a new instance.
     *
     * @param ICollection $renderSettings The render setting.
     *
     * @param IItem       $item           The item.
     *
     * @param string      $desiredFormat  The desired format.
     *
     * @param array       $result         The parsed result.
     */
    public function __construct(ICollection $renderSettings, IItem $item, $desiredFormat, array $result)
    {
        $this->renderSettings = $renderSettings;
        $this->item           = $item;
        $this->desiredFormat  = $desiredFormat;
        $this->result         = $result;
    }

    /**
     * Retrieve the render setting collection.
     *
     * @return ICollection
     */
    public function getRenderSettings()
    {
        return $this->renderSettings;
    }

    /**
     * Retrieve the item.
     *
     * @return IItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Retrieve the desired format.
     *
     * @return string
     */
    public function getDesiredFormat()
    {
        return $this->desiredFormat;
    }

    /**
     * Retrieve the result array.
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set the result array.
     *
     * @param array $result The new result.
     *
     * @return ParseItemEvent
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
