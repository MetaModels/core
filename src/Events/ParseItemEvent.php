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

namespace MetaModels\Events;

use MetaModels\IItem;
use MetaModels\Render\Setting\ICollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered when a MetaModels item is parsed.
 */
class ParseItemEvent extends Event
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
