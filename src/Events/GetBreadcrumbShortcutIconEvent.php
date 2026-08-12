<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered for every shortcut of the breadcrumb, so that the icon can be exchanged.
 *
 * The shortcuts lead from a MetaModel to its areas, and their icons come from the operations of
 * tl_metamodel - the same ones the list of all MetaModels shows. Those are fixed, while an area
 * may well want to say something about the MetaModel at hand: whether it holds anything.
 *
 * The MetaModel is named by its id, and the area by the name of the operation leading to it.
 */
final class GetBreadcrumbShortcutIconEvent extends Event
{
    /**
     * The event name.
     */
    public const NAME = 'metamodels.breadcrumb.get-shortcut-icon';

    /**
     * The name of the operation the shortcut stands for.
     *
     * @var string
     */
    private string $operationName;

    /**
     * The id of the MetaModel the shortcut belongs to.
     *
     * @var string
     */
    private string $metaModelId;

    /**
     * The icon of the shortcut.
     *
     * @var string
     */
    private string $icon;

    /**
     * Create a new instance.
     *
     * @param string $operationName The name of the operation the shortcut stands for.
     * @param string $metaModelId   The id of the MetaModel the shortcut belongs to.
     * @param string $icon          The icon as declared by the operation.
     */
    public function __construct(string $operationName, string $metaModelId, string $icon)
    {
        $this->operationName = $operationName;
        $this->metaModelId   = $metaModelId;
        $this->icon          = $icon;
    }

    /**
     * Retrieve the name of the operation the shortcut stands for.
     *
     * @return string
     */
    public function getOperationName()
    {
        return $this->operationName;
    }

    /**
     * Retrieve the id of the MetaModel the shortcut belongs to.
     *
     * @return string
     */
    public function getMetaModelId()
    {
        return $this->metaModelId;
    }

    /**
     * Retrieve the icon of the shortcut.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the icon of the shortcut.
     *
     * @param string $icon The icon.
     *
     * @return $this
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }
}
