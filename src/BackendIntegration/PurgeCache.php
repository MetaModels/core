<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Purge the MetaModels cache.
 */
class PurgeCache
{
    /**
     * The cache directory.
     *
     * @var string
     */
    private $cacheDir;

    /**
     * The logger to use.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Create a new instance.
     *
     * @param string          $cacheDir The cache directory.
     * @param LoggerInterface $logger   The logger.
     */
    public function __construct($cacheDir, LoggerInterface $logger)
    {
        $this->cacheDir = $cacheDir;
        $this->logger   = $logger;
    }

    /**
     * Purge the file cache.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function purge()
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($this->cacheDir)) {
            return;
        }
        $fileSystem->remove($this->cacheDir);

        $this->logger->log(
            LogLevel::INFO,
            'Purged the MetaModels cache',
            ['contao' => new ContaoContext(__METHOD__, TL_CRON)]
        );
    }
}
