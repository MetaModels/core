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

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Purge the MetaModels cache.
 */
class PurgeCache
{
    /**
     * Purge the page cache.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function purge()
    {
        foreach ($GLOBALS['TL_PURGE']['folders']['metamodels']['affected'] as $folderName) {
            // Purge the folder
            $folder = new \Folder($folderName);
            $folder->purge();
        }

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $GLOBALS['container']['event-dispatcher'];
        $dispatcher->dispatch(
            ContaoEvents::SYSTEM_LOG,
            new LogEvent('Purged the MetaModels cache', __METHOD__, TL_CRON)
        );
    }
}
