<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\Helper\OperationButtonCallbackListener;
use MetaModels\Helper\ToolboxFile;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Collects the dca combinations for each MetaModel, that is matching the current user.
 */
class MetaModelDcaBuilder
{
    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $container;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $container The container.
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Get Contao Database instance.
     *
     * @return \Database
     */
    protected function getDB()
    {
        return $this->container->getDatabase();
    }

    /**
     * Retrieve the event dispatcher from the DIC.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDispatcher()
    {
        return $this->container->getEventDispatcher();
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
    public function getBackendIcon(
        $icon,
        $defaultIcon = 'system/modules/metamodels/assets/images/icons/metamodels.png'
    ) {
        $dispatcher = $this->getDispatcher();
        $realIcon   = ToolboxFile::convertValueToPath($icon);
        // Determine image to use.
        if ($realIcon && file_exists(TL_ROOT . '/' . $realIcon)) {
            $event = new ResizeImageEvent($realIcon, 16, 16);
            $dispatcher->dispatch(ContaoEvents::IMAGE_RESIZE, $event);
            return $event->getResultImage();
        }

        return $defaultIcon;
    }

    /**
     * Inject an input screen into the DCA of a table.
     *
     * @param IInputScreen $screen The input screen that shall get injected.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function injectOperationButton($screen)
    {
        $parentTable = $screen->getParentTable();
        $parentDCA   = &$GLOBALS['TL_DCA'][$parentTable];
        $dispatcher  = $this->getDispatcher();
        $metaModel   = $screen->getMetaModel();
        $event       = new LoadLanguageFileEvent('default');
        $dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);

        $arrCaption = array(
            sprintf(
                $GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'],
                $metaModel->getName()
            ),
            ''
        );

        foreach ($screen->getBackendCaption() as $arrLangEntry) {
            if ($arrLangEntry['label'] != '' && $arrLangEntry['langcode'] == $GLOBALS['TL_LANGUAGE']) {
                $arrCaption = array($arrLangEntry['label'], $arrLangEntry['description']);
            }
        }

        $parentDCA['list']['operations']['edit_' . $metaModel->getTableName()] = array
        (
            'label'               => &$arrCaption,
            'href'                => 'table='.$metaModel->getTableName(),
            'icon'                => $this->getBackendIcon($screen->getIcon()),
            'attributes'          => 'onclick="Backend.getScrollOffset()"',
        );

        $operationName = 'edit_' . $metaModel->getTableName();
        // Is the destination table a metamodel with variants?
        if ($metaModel->hasVariants()) {
            $parentDCA['list']['operations'][$operationName]['idparam'] = 'id_' . $parentTable;
        } else {
            $parentDCA['list']['operations'][$operationName]['idparam'] = 'pid';
        }

        // Compatibility with DC_Table.
        if ($parentDCA['config']['dataContainer'] !== 'General') {
            $handler     = $this;
            $idParameter = $parentDCA['list']['operations'][$operationName]['idparam'];

            $parentDCA['list']['operations'][$operationName]['button_callback'] =
                OperationButtonCallbackListener::generateFor(
                    $parentTable,
                    $operationName,
                    function ($row, $href, $label, $name, $icon, $attributes, $table) use ($handler, $idParameter) {
                        return $handler->buildChildOperationButton(
                            $idParameter,
                            $row,
                            $href,
                            $label,
                            $name,
                            $icon,
                            $attributes,
                            $table
                        );
                    }
                );
        }
    }

    /**
     * This method exists only for being compatible when MetaModels are being used as child table from DC_Table context.
     *
     * @param string $idParameter The id parameter in use.
     *
     * @param array  $arrRow      The current data row.
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
    public function buildChildOperationButton($idParameter, $arrRow, $href, $label, $name, $icon, $attributes, $table)
    {
        $dispatcher = $this->getDispatcher();
        $modelId    = ModelId::fromValues($table, $arrRow['id']);
        $urlEvent   = new AddToUrlEvent($href. '&amp;' . $idParameter . '=' . $modelId->getSerialized());

        $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

        $imageEvent = new GenerateHtmlEvent($icon, $label);
        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $imageEvent);

        $title = sprintf($label ?: $name, $arrRow['id']);
        return '<a href="' . $urlEvent->getUrl() . '" title="' .
            specialchars($title) . '"' . $attributes . '>' . $imageEvent->getHtml() .
        '</a> ';
    }
}
