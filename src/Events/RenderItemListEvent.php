<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use MetaModels\ItemList;
use MetaModels\Render\Template;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is fired when a MetaModels list get's rendered.
 */
class RenderItemListEvent extends Event
{
    /**
     * The item list getting rendered.
     *
     * @var ItemList
     */
    private ItemList $list;

    /**
     * The list template being rendered.
     *
     * @var Template
     */
    private Template $template;

    /**
     * The calling object (most likely a Module or ContentElement).
     *
     * @var object|null
     */
    private object|null $caller;

    /**
     * Create a new instance.
     *
     * @param ItemList    $list     The item list getting rendered.
     * @param Template    $template The list template.
     * @param object|null $caller   The calling object (most likely a Module or ContentElement).
     */
    public function __construct(ItemList $list, Template $template, $caller = null)
    {
        $this->list     = $list;
        $this->template = $template;
        $this->caller   = $caller;
    }

    /**
     * Retrieve the list being rendered.
     *
     * @return ItemList
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Retrieve the template instance.
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Retrieve the caller.
     *
     * @return object|null
     */
    public function getCaller()
    {
        return $this->caller;
    }
}
