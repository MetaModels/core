<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Doctrine\DBAL\Connection;
use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\MetaModelsEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base event listener to boot up a MetaModelServiceContainer.
 */
class SubSystemBootListener
{

    /**
     * The Contao framework.
     *
     * @var ContaoFramework
     */
    private $contaoFramework;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The scope matcher.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeMatcher;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * SubSystemBoot constructor.
     *
     * @param ContaoFramework          $contaoFramework The Contao framework.
     * @param Connection               $connection      The database connection.
     * @param LoggerInterface          $logger          The logger.
     * @param RequestScopeDeterminator $scopeMatcher    The scope matcher.
     * @param EventDispatcherInterface $dispatcher      The event dispatcher.
     */
    public function __construct(
        ContaoFramework $contaoFramework,
        Connection $connection,
        LoggerInterface $logger,
        RequestScopeDeterminator $scopeMatcher,
        EventDispatcherInterface $dispatcher
    ) {
        $this->contaoFramework = $contaoFramework;
        $this->connection      = $connection;
        $this->logger          = $logger;
        $this->scopeMatcher    = $scopeMatcher;
        $this->dispatcher      = $dispatcher;
    }

    /**
     * Boot up the system.
     *
     * @return void
     */
    public function boot(): void
    {
        /** @var Environment $environment */
        $environment = $this->contaoFramework->getAdapter(Environment::class);
        $script      = explode('?', $environment->get('relativeRequest'), 2)[0];

        // There is no need to boot in login or install screen.
        if (('contao/login' === $script) || ('contao/install' === $script)) {
            return;
        }

        try {
            if (!$this->connection->getSchemaManager()->tablesExist(
                [
                    'tl_metamodel',
                    'tl_metamodel_dca',
                    'tl_metamodel_dca_sortgroup',
                    'tl_metamodel_dcasetting',
                    'tl_metamodel_dcasetting_condition',
                    'tl_metamodel_attribute',
                    'tl_metamodel_filter',
                    'tl_metamodel_filtersetting',
                    'tl_metamodel_rendersettings',
                    'tl_metamodel_rendersetting',
                    'tl_metamodel_dca_combine',
                ]
            )) {
                $this->logger->error('MetaModels startup interrupted. Not all MetaModels tables have been created.');
                return;
            }
        } catch (\Throwable $throwable) {
            // Swallow and return - we might not be installed yet. See #1376.
            $this->logger->error('MetaModels startup interrupted. Exception occurred while checking tables.');
            return;
        }

        $event = new MetaModelsBootEvent();
        $this->tryDispatch(MetaModelsEvents::SUBSYSTEM_BOOT, $event);

        switch (true) {
            case $this->scopeMatcher->currentScopeIsFrontend():
                $this->tryDispatch(MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND, $event);
                break;
            case $this->scopeMatcher->currentScopeIsBackend():
                $this->tryDispatch(MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND, $event);
                break;
            default:
        }
    }

    /**
     * Test if the event must get dispatched, if so, trigger deprecation and dispatch then.
     *
     * @param string              $eventName The event name.
     * @param MetaModelsBootEvent $event     The event payload.
     *
     * @return void
     */
    private function tryDispatch($eventName, MetaModelsBootEvent $event): void
    {
        if ($this->dispatcher->hasListeners($eventName)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Event "' . $eventName . '" has been deprecated - Use registered services.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->dispatcher->dispatch($event, $eventName);
        }
    }
}
