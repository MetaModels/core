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

namespace MetaModels\DcGeneral\Events\Table\InputScreen;

use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;

/**
 * Handle event to update model in table tl_metamodel_dca.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */
class UpdateInputScreen
{
    /**
     * Handle the update of a MetaModel and all attached data.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public static function handle(PostPersistModelEvent $event)
    {
        $new = $event->getModel();

        if (!$new->getProperty('isdefault')) {
            return;
        }

        \Database::getInstance()
            ->prepare('UPDATE tl_metamodel_dca
                SET isdefault = \'\'
                WHERE pid=?
                    AND id<>?
                    AND isdefault=1')
            ->execute(
                $new->getProperty('pid'),
                $new->getId()
            );
    }
}
