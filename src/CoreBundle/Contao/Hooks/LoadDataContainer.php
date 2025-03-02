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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\Helper\LocaleUtil;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class handles loading of the virtual data containers.
 */
class LoadDataContainer
{
    /**
     * Adapter to the Contao\Controller class.
     *
     * @var Adapter<Controller>
     */
    private Adapter $controller;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    private ViewCombination $combination;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private IconBuilder $iconBuilder;

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
    public function onLoadDataContainer($tableName): void
    {
        $scopeMatcher = System::getContainer()->get('cca.dc-general.scope-matcher');
        if (!($scopeMatcher instanceof RequestScopeDeterminator) || !$scopeMatcher->currentScopeIsBackend()) {
            return;
        }

        static $tableExists;
        // Test that the tables have been created.
        if (null === $tableExists) {
            if (null === ($connection = System::getContainer()->get('database_connection'))) {
                return;
            }
            $tableExists = $connection->createSchemaManager()->tablesExist(['tl_metamodel']);
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
    private function handleMetaModelTable(string $tableName): void
    {
        static $tableNames;
        if (!$tableNames) {
            $tableNames = $this->factory->collectNames();
        }
        // Not a MetaModel, get out now.
        if (!\in_array($tableName, $tableNames)) {
            return;
        }

        $this->controller->loadDataContainer('tl_metamodel_item');

        if (!isset($GLOBALS['TL_DCA'][$tableName])) {
            $GLOBALS['TL_DCA'][$tableName] = [];
        }

        $GLOBALS['TL_DCA'][$tableName] = \array_replace_recursive(
            (array) ($GLOBALS['TL_DCA']['tl_metamodel_item'] ?? []),
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function handleNonMetaModelTable(string $tableName): void
    {
        // Nothing to do for MetaModel tables.
        if (\str_starts_with($tableName, 'mm_')) {
            return;
        }

        static $map;
        if (!isset($map)) {
            $map = $this->buildMap();
        }

        // No children for this table.
        if (!isset($map[$tableName])) {
            return;
        }

        $parentDCA = &$GLOBALS['TL_DCA'][$tableName];

        foreach ($map[$tableName] as $metaModelTable => $inputScreen) {
            $metaModel = $this->factory->getMetaModel($metaModelTable);
            assert($metaModel instanceof IMetaModel);

            $translationPrefix = 'metamodel_edit_as_child.'
                . $metaModel->getTableName()
                . '.' . $inputScreen['meta']['id'];

            $operationName                                   = 'edit_' . $metaModel->getTableName();
            $parentDCA['list']['operations'][$operationName] = [
                'href'       => 'table=' . $metaModelTable,
                'icon'       => $this->iconBuilder->getBackendIcon($inputScreen['meta']['backendicon']),
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ];

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
                    function (
                        array $row,
                        string $href,
                        string $label,
                        string $name,
                        string $icon,
                        string $attributes,
                        string $table
                    ) use (
                        $idParameter,
                        $translationPrefix
                    ): string {
                        return $this->buildChildOperationButton(
                            $idParameter,
                            $row['id'],
                            $href,
                            $translationPrefix,
                            $icon,
                            $attributes,
                            $table
                        );
                    };
            }
        }
    }

    /**
     * Build the data container map.
     *
     * @return array
     */
    private function buildMap(): array
    {
        $map = [];
        foreach ($this->combination->getParented() as $childName => $child) {
            $map[$child['meta']['ptable']][$childName] = $child;
        }

        return $map;
    }

    /**
     * This method exists only for being compatible when MetaModels are being used as child table from DC_Table context.
     *
     * @param string $idParameter The id parameter in use.
     * @param string $itemId      The current data row.
     * @param string $href        The href to be appended.
     * @param string $transPrefix The operation button label translation prefix.
     * @param string $icon        The icon path.
     * @param string $attributes  The button attributes.
     * @param string $table       The table name.
     *
     * @return string
     */
    private function buildChildOperationButton(
        string $idParameter,
        string $itemId,
        string $href,
        string $transPrefix,
        string $icon,
        string $attributes,
        string $table
    ): string {
        $modelId = ModelId::fromValues($table, $itemId);

        $url = $href . '&amp;' . $idParameter . '=' . $modelId->getSerialized();
        // If id parameter different, we have to override it in the URL.
        if ('id' !== $idParameter) {
            $url .= '&amp;id=';
        }
        $url = $this->controller->addToUrl($url);
        // If id parameter different, we have to clean out the id in the URL now.
        if ('id' !== $idParameter) {
            $url = \preg_replace('#(&amp;)id=(?:&amp;)?#', '$1', $url);
        }

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        $label = $translator->trans(
            $transPrefix . '.label',
            ['%id%' => $itemId],
            $table
        );
        $title = $translator->trans(
            $transPrefix . '.description',
            ['%id%' => $itemId],
            $table
        );

        return \sprintf(
            '<a href="%1$s" title="%2$s"%3$s>%4$s</a> ',
            $url,
            StringUtil::specialchars($title),
            $attributes,
            $this->iconBuilder->getBackendIconImageTag($icon, $label)
        );
    }
}
