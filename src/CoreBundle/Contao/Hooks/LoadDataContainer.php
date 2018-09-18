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

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\IFactory;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This class handles loading of the virtual data containers.
 */
class LoadDataContainer
{
    /**
     * Adapter to the Contao\Controller class.
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
     * The view combination.
     *
     * @var ViewCombination
     */
    private $combination;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * Create a new instance.
     *
     * @param IFactory        $factory           The MetaModels factory.
     * @param ViewCombination $combination       The view combination provider.
     * @param Adapter         $controllerAdapter The controller adapter to load languages and data containers.
     * @param IconBuilder     $iconBuilder       The icon builder.
     */
    public function __construct(
        IFactory $factory,
        ViewCombination $combination,
        Adapter $controllerAdapter,
        IconBuilder $iconBuilder
    ) {
        $this->factory     = $factory;
        $this->combination = $combination;
        $this->controller  = $controllerAdapter;
        $this->iconBuilder = $iconBuilder;
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
        static $tableExists;
        // Test that the tables have been created.
        if (null === $tableExists) {
            $tableExists = \System::getContainer()
                ->get('database_connection')
                ->getSchemaManager()
                ->tablesExist(['tl_metamodel']);
        }
        if (false === $tableExists) {
            return;
        }

        $this->handleMetaModelTable($tableName);
        $this->handleNonMetaModelTable($tableName);
    }

    /**
     * Handle for MetaModel tables - this loads the base definition "tl_metamodel_item".
     *
     * @param string $tableName The table name.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function handleMetaModelTable($tableName)
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

    /**
     * Handle for non MetaModel tables - this adds the child operations.
     *
     * @param string $tableName The table name.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function handleNonMetaModelTable($tableName)
    {
        // Nothing to do for MetaModel tables.
        if (substr($tableName, 0, 3) === 'mm_') {
            return;
        }

        static $map;
        if (!isset($map)) {
            $parented = $this->combination->getParented();
            foreach ($parented as $childName => $child) {
                $map[$child['meta']['ptable']][$childName] = $child;
            }
        }

        // No children for this table.
        if (!isset($map[$tableName])) {
            return;
        }

        $parentDCA = &$GLOBALS['TL_DCA'][$tableName];

        $this->controller->loadLanguageFile('default');
        foreach ($map[$tableName] as $metaModelTable => $inputScreen) {
            $metaModel = $this->factory->getMetaModel($metaModelTable);

            // FIXME: need a proper translator here. :/
            $caption = [
                sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'], $metaModel->getName()),
                ''
            ];

            foreach ($inputScreen['label'] as $langCode => $label) {
                // FIXME: need the correct language here.
                if ($label != '' && $langCode == $GLOBALS['TL_LANGUAGE']) {
                    $caption = [$label, $inputScreen['description'][$langCode]];
                }
            }

            $operationName                                   = 'edit_' . $metaModel->getTableName();
            $parentDCA['list']['operations'][$operationName] = array
            (
                'label'      => &$caption,
                'href'       => 'table=' . $metaModelTable,
                'icon'       => $this->iconBuilder->getBackendIcon($inputScreen['meta']['backendicon']),
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            );

            // Is the destination table a metamodel with variants?
            if ($metaModel->hasVariants()) {
                $parentDCA['list']['operations'][$operationName]['idparam'] = 'id_' . $tableName;
            } else {
                $parentDCA['list']['operations'][$operationName]['idparam'] = 'pid';
            }

            // Compatibility with DC_Table.
            if ($parentDCA['config']['dataContainer'] !== 'General') {
                $idParameter                                                        =
                    $parentDCA['list']['operations'][$operationName]['idparam'];
                $parentDCA['list']['operations'][$operationName]['button_callback'] =
                    function ($row, $href, $label, $name, $icon, $attributes, $table) use ($idParameter) {
                        return $this->buildChildOperationButton(
                            $idParameter,
                            $row['id'],
                            $href,
                            $label,
                            $name,
                            $icon,
                            $attributes,
                            $table
                        );
                    };
            }
        }
    }

    /**
     * This method exists only for being compatible when MetaModels are being used as child table from DC_Table context.
     *
     * @param string $idParameter The id parameter in use.
     *
     * @param string $itemId      The current data row.
     *
     * @param string $href        The href to be appended.
     *
     * @param string $label       The operation label.
     *
     * @param string $name        The operation name.
     *
     * @param string $icon        The icon path.
     *
     * @param string $attributes  The button attributes.
     *
     * @param string $table       The table name.
     *
     * @return string
     */
    private function buildChildOperationButton($idParameter, $itemId, $href, $label, $name, $icon, $attributes, $table)
    {
        $modelId = ModelId::fromValues($table, $itemId);

        $url = $href . '&amp;' . $idParameter . '=' . $modelId->getSerialized();
        // If id parameter different, we have to override it in the URL.
        if ('id' !== $idParameter) {
            $url .= '&amp;id=';
        }
        $url = $this->controller->addToUrl($url);
        // If id parameter different, we have to clean out the id in the URL now.
        if ('id' !== $idParameter) {
            $url = preg_replace('#(&amp;)id=(?:&amp;)?#', '$1', $url);
        }

        $title = sprintf($label ?: $name, $itemId);
        return sprintf(
            '<a href="%1$s" title="%2$s"%3$s>%4$s</a> ',
            $url,
            specialchars($title),
            $attributes,
            $this->iconBuilder->getBackendIconImageTag($icon, $label)
        );
    }
}
