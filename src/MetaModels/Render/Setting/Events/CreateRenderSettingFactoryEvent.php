<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Render\Setting\Events;

use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for every render setting factory instance that is created.
 */
class CreateRenderSettingFactoryEvent extends Event
{
    /**
     * The factory that has been created.
     *
     * @var IRenderSettingFactory
     */
    protected $factory;

    /**
     * Create a new instance.
     *
     * @param IRenderSettingFactory $factory The factory that has been created.
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve the attribute information array.
     *
     * @return IRenderSettingFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }
}
