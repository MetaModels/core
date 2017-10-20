<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use MetaModels\IFactory;

/**
 * This class handles loading of the virtual data containers.
 */
class LoadDataContainer
{
    /**
     * Adapter to the Contao\System class.
     *
     * @var Controller
     */
    private $controller;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory           The MetaModels factory.
     * @param Adapter  $controllerAdapter The controller adapter to load languages and data containers.
     */
    public function __construct(IFactory $factory, Adapter $controllerAdapter)
    {
        $this->controller = $controllerAdapter;
        $this->factory    = $factory;
    }

    /**
     * Load a data container.
     *
     * @param string $tableName The table name.
     *
     * @return void
     */
    public function onLoadDataContainer($tableName)
    {
        static $tableNames;
        if (!$tableNames) {
            $tableNames = $this->factory->collectNames();
        }
        // Not a MetaModel, get out now.
        if (!in_array($tableName, $tableNames)) {
            return;
        }

        $this->controller->loadLanguageFile('tl_metamodel_item');
        $this->controller->loadDataContainer('tl_metamodel_item');
        if (!isset($GLOBALS['TL_DCA'][$tableName])) {
            $GLOBALS['TL_DCA'][$tableName] = [];
        }

        $GLOBALS['TL_DCA'][$tableName] = array_replace_recursive(
            (array) $GLOBALS['TL_DCA']['tl_metamodel_item'],
            (array) $GLOBALS['TL_DCA'][$tableName]
        );
    }
}
