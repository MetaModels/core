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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use Contao\CoreBundle\Monolog\ContaoContext;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class PurgeTranslator
{
    public function __construct(
        private readonly string $cacheDir,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }


    /**
     * Purge the symfony translator.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function purge()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->cacheDir);
        $this->dispatcher->dispatch(
            new LogEvent('Purged the Symfony translator', __METHOD__, ContaoContext::CRON),
            ContaoEvents::SYSTEM_LOG
        );
    }
}
