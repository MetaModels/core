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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSortGroup;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * This provides the attribute name options.
 */
class AttributeOptionsListener extends AbstractListener
{
    /**
     * Provide options for attribute type selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $result    = [];
        $metaModel = $this->getMetaModelFromModel($event->getModel());

        foreach ($metaModel->getAttributes() as $attribute) {
            $typeName                      = $attribute->get('type');
            $result[$attribute->get('id')] = $attribute->getName() . ' [' . $typeName . ']';
        }

        $event->setOptions($result);
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        /** @var GetPropertyOptionsEvent $event */
        return parent::wantToHandle($event)
            && in_array($event->getPropertyName(), ['rendergroupattr', 'rendersortattr']);
    }
}
