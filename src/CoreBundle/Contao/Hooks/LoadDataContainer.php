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
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\Image;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MetaModels\Helper\ToolboxFile;
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
     * The image factory.
     *
     * @var ImageFactoryInterface
     */
    private $imageFactory;

    /**
     * The image adapter.
     *
     * @var Image
     */
    private $image;

    /**
     * Create a new instance.
     *
     * @param IFactory              $factory           The MetaModels factory.
     * @param ViewCombination       $combination       The view combination provider.
     * @param ImageFactoryInterface $imageFactory      The image factory for resizing images.
     * @param Adapter               $controllerAdapter The controller adapter to load languages and data containers.
     * @param Adapter               $imageAdapter      The image adapter to generate HTML code for buttons.
     */
    public function __construct(
        IFactory $factory,
        ViewCombination $combination,
        ImageFactoryInterface $imageFactory,
        Adapter $controllerAdapter,
        Adapter $imageAdapter
    ) {
        $this->factory      = $factory;
        $this->combination  = $combination;
        $this->imageFactory = $imageFactory;
        $this->controller   = $controllerAdapter;
        $this->image        = $imageAdapter;
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
        $this->handleMetaModelTable($tableName);
        $this->handleNonMetaModelTable($tableName);
    }

    /**
     * Handle for MetaModel tables - this loads the base definition "tl_metamodel_item".
     *
     * @param string $tableName The table name.
     *
     * @return void
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
            (array)$GLOBALS['TL_DCA']['tl_metamodel_item'],
            (array)$GLOBALS['TL_DCA'][$tableName]
        );
    }

    /**
     * Handle for non MetaModel tables - this adds the child operations.
     *
     * @param string $tableName The table name.
     *
     * @return void
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

        $parentDCA   = &$GLOBALS['TL_DCA'][$tableName];

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

            $operationName = 'edit_' . $metaModel->getTableName();
            $parentDCA['list']['operations'][$operationName] = array
            (
                'label'               => &$caption,
                'href'                => 'table=' . $metaModelTable,
                'icon'                => $this->getBackendIcon($inputScreen['meta']['backendicon']),
                'attributes'          => 'onclick="Backend.getScrollOffset()"',
            );

            // Is the destination table a metamodel with variants?
            if ($metaModel->hasVariants()) {
                $parentDCA['list']['operations'][$operationName]['idparam'] = 'id_' . $tableName;
            } else {
                $parentDCA['list']['operations'][$operationName]['idparam'] = 'pid';
            }

            // Compatibility with DC_Table.
            if ($parentDCA['config']['dataContainer'] !== 'General') {
                $idParameter = $parentDCA['list']['operations'][$operationName]['idparam'];
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
     * Get a 16x16 pixel resized icon of the passed image if it exists, return the default icon otherwise.
     *
     * @param string $icon        The icon to resize.
     *
     * @param string $defaultIcon The default icon.
     *
     * @return string
     */
    public function getBackendIcon($icon, $defaultIcon = 'bundles/metamodelscore/images/icons/metamodels.png')
    {
        $realIcon   = ToolboxFile::convertValueToPath($icon);
        // Determine image to use.
        if ($realIcon && file_exists(TL_ROOT . '/' . $realIcon)) {
            return $this->imageFactory->create($realIcon, [16, 16, 'proportional']);
        }

        return $defaultIcon;
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
    public function buildChildOperationButton($idParameter, $itemId, $href, $label, $name, $icon, $attributes, $table)
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
            $this->image->getHtml($icon, $label)
        );
    }
}
