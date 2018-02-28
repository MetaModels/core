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

namespace MetaModels\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Webmozart\PathUtil\Path;

/**
 * Adds the Contao configuration structure.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * The debug flag.
     *
     * @var bool
     */
    private $debug;

    /**
     * The root directory.
     *
     * @var string
     */
    private $rootDir;

    /**
     * Constructor.
     *
     * @param bool   $debug   The debug flag.
     * @param string $rootDir The root directory.
     */
    public function __construct($debug, $rootDir)
    {
        $this->debug   = (bool) $debug;
        $this->rootDir = $rootDir;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('metamodels');

        $rootNode
            ->children()
                ->booleanNode('enable_cache')
                    ->defaultValue(!$this->debug)
                ->end()
                ->scalarNode('cache_dir')
                    ->defaultValue('%kernel.cache_dir%/metamodels')
                ->end()
                ->scalarNode('assets_dir')
                    ->cannotBeEmpty()
                    ->defaultValue($this->resolvePath($this->rootDir . '/assets/metamodels'))
                    ->validate()
                        ->always(function ($value) {
                            return $this->resolvePath($value);
                        })
                    ->end()
                ->end()
                ->scalarNode('assets_web')
                    ->cannotBeEmpty()
                    ->defaultValue($this->resolvePath('assets/metamodels'))
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * Resolves a path.
     *
     * @param string $value The path.
     *
     * @return string
     */
    private function resolvePath($value)
    {
        $path = Path::canonicalize($value);

        if ('\\' === DIRECTORY_SEPARATOR) {
            $path = str_replace('/', '\\', $path);
        }

        return $path;
    }
}
