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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered when a MetaModel id must get translated to a MetaModel name.
 */
class GetMetaModelNameFromIdEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.metamodel.get-metamodel-name-from-id';

    /**
     * The MetaModel id to be translated.
     *
     * @var string
     */
    protected $metaModelId;

    /**
     * The name of the MetaModel.
     *
     * @var string
     */
    protected $metaModelName;

    /**
     * Create a new instance.
     *
     * @param string $metaModelId The id to be translated.
     */
    public function __construct($metaModelId)
    {
        $this->metaModelId = $metaModelId;
    }

    /**
     * Retrieve the MetaModel id.
     *
     * @return string
     */
    public function getMetaModelId()
    {
        return $this->metaModelId;
    }

    /**
     * Retrieve the MetaModel name.
     *
     * @return string
     */
    public function getMetaModelName()
    {
        return $this->metaModelName;
    }

    /**
     * Set the MetaModel name.
     *
     * @param string $metaModelName The name of the MetaModel.
     *
     * @return GetMetaModelNameFromIdEvent
     */
    public function setMetaModelName($metaModelName)
    {
        $this->metaModelName = $metaModelName;

        return $this;
    }
}
