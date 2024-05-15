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

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IItem;

/**
 * This class retrieves the options of an attribute within a MetaModel unless someone else already provided them.
 */
class PropertyOptionsProvider
{
    /**
     * Retrieve the property options.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getPropertyOptions(GetPropertyOptionsEvent $event)
    {
        if (null !== $event->getOptions()) {
            return;
        }
        $model = $event->getModel();
        if (!($model instanceof Model)) {
            return;
        }

        $propertyName = $event->getPropertyName();
        assert(\is_string($propertyName));

        $item = $model->getItem();
        assert($item instanceof IItem);

        $attribute = $item->getAttribute($propertyName);
        if (!($attribute instanceof IAttribute)) {
            return;
        }

        try {
            $options = $attribute->getFilterOptions(null, false);
        } catch (\Exception $exception) {
            $options = ['Error: ' . $exception->getMessage()];
        }

        $event->setOptions($options);
    }
}
