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

namespace MetaModels\DcGeneral\Events\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Event handler to populate the options array of the filters.
 *
 * @package MetaModels\DcGeneral\Events\Table\SearchablePages
 */
class RenderSettings
{
    /**
     * Provide options for filter list.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getOptions(GetPropertyOptionsEvent $event)
    {
        $model = $event->getModel();
        $pid   = $model->getProperty('pid');
        if (empty($pid)) {
            return;
        }

        $filter = \Database::getInstance()
            ->prepare('SELECT id, name FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($pid);

        $options = array();
        while ($filter->next()) {
            $options[$filter->id] = $filter->name;
        }

        $event->setOptions($options);
    }
}
