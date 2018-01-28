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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;

/**
 * This class takes care of updating all data when an attribute has been saved.
 */
class AttributeSavedListener extends BaseListener
{
    /**
     * Handle the update of an attribute and all attached data.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PostPersistModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $old         = $event->getOriginalModel();
        $new         = $event->getModel();
        $oldInstance = $old->getProperty('pid') ? $this->createAttributeInstance($old) : null;
        $newInstance = $this->createAttributeInstance($new);

        // If type or column name has been changed, destroy old data and initialize new.
        if ($this->isAttributeNameOrTypeChanged($new, $old)) {
            // Destroy old instance.
            if ($oldInstance) {
                $oldInstance->destroyAUX();
            }

            // Create new instance aux info.
            if ($newInstance) {
                $newInstance->initializeAUX();
            }
        }

        if ($newInstance) {
            // Now loop over all values and update the meta in the instance.
            foreach ($new->getPropertiesAsArray() as $strKey => $varValue) {
                $newInstance->handleMetaChange($strKey, $varValue);
            }
        }
    }

    /**
     * Check if either type or column name have been changed within the model.
     *
     * @param ModelInterface $new The new model.
     * @param ModelInterface $old The old model (or null).
     *
     * @return bool
     */
    private function isAttributeNameOrTypeChanged(ModelInterface $new, ModelInterface $old)
    {
        return ($old->getProperty('type') !== $new->getProperty('type'))
            || ($old->getProperty('colname') !== $new->getProperty('colname'));
    }
}
