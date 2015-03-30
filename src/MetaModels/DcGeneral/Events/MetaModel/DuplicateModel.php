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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;

/**
 * This class handles the paste into or after handling for variants.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class DuplicateModel extends BaseSubscriber
{
    /**
     * Register all listeners.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this->addListener(
            PostDuplicateModelEvent::NAME,
            array($this, 'handle')
        );
    }

    /**
     * Handle the paste into and after event.
     *
     * @param PostDuplicateModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PostDuplicateModelEvent $event)
    {
        $model = $event->getModel();

        $metaModel = $this
            ->getServiceContainer()
            ->getFactory()
            ->getMetaModel($model->getProviderName());

        if (!$metaModel || !$metaModel->hasVariants()) {
            return;
        }

        // Set the vargroup to null for auto creating.
        $model->setProperty('vargroup', null);
    }
}
