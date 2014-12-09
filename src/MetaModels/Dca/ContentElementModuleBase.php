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
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\UrlBuilder\Contao\BackendUrlBuilder;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\Factory as MetaModelFactory;
use MetaModels\Filter\Setting\Factory as FilterFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Complementary methods needed by the DCA in tl_module and tl_content.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
class ContentElementModuleBase
{
    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getEventDispatcher()
    {
        return $GLOBALS['container']['event-dispatcher'];
    }

    /**
     * Called from subclass.
     *
     * @param \DC_Table $dataContainer The data container calling this method.
     *
     * @param string    $table         The table name.
     *
     * @param string    $elementName   The type name to search for.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildFilterParamsFor(\DC_Table $dataContainer, $table, $elementName)
    {
        $element = \Database::getInstance()
            ->prepare(
                'SELECT    c.metamodel_filtering
                FROM    ' . $table . ' AS c
                JOIN    tl_metamodel AS mm ON mm.id = c.metamodel
                WHERE    c.id = ?
                AND        c.type = ?'
            )
            ->limit(1)
            ->execute($dataContainer->id, $elementName);

        if (!$element->metamodel_filtering) {
            unset($GLOBALS['TL_DCA'][$table]['fields']['metamodel_filterparams']);
            return;
        }

        $objFilterSettings = FilterFactory::byId($element->metamodel_filtering);

        $GLOBALS['TL_DCA'][$table]['fields']['metamodel_filterparams']['eval']['subfields'] =
            $objFilterSettings->getParameterDCA();
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @param string    $table         The table name.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editMetaModelButton(\DC_Table $dataContainer, $table)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $event = new GenerateHtmlEvent(
            'alias.gif',
            $GLOBALS['TL_LANG'][$table]['editmetamodel'][0],
            'style="vertical-align:top"'
        );

        $dispatcher = $this->getEventDispatcher();
        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

        $url = BackendUrlBuilder::fromUrl('contao/main.php?do=metamodels&act=edit')
            ->setQueryParameter('id', IdSerializer::fromValues('tl_metamodel', $dataContainer->value)->getSerialized());

        return sprintf(
            '<a href="%s" title="%s" style="padding-left:3px">%s</a>',
            $url->getUrl(),
            sprintf(specialchars($GLOBALS['TL_LANG'][$table]['editmetamodel'][1]), $dataContainer->value),
            $event->getHtml()
        );
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @param string    $table         The table name.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editFilterSettingButton(\DC_Table $dataContainer, $table)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $event = new GenerateHtmlEvent(
            'alias.gif',
            $GLOBALS['TL_LANG'][$table]['editfiltersetting'][0],
            'style="vertical-align:top"'
        );

        $dispatcher = $this->getEventDispatcher();

        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

        $url = BackendUrlBuilder::fromUrl('contao/main.php?do=metamodels&table=tl_metamodel_filtersetting')
            ->setQueryParameter(
                'pid',
                IdSerializer::fromValues('tl_metamodel_filter', $dataContainer->value)->getSerialized()
            );

        return sprintf(
            '<a href="%s" title="%s" style="padding-left:3px">%s</a>',
            $url->getUrl(),
            sprintf(specialchars($GLOBALS['TL_LANG'][$table]['editfiltersetting'][1]), $dataContainer->value),
            $event->getHtml()
        );
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @param string    $table         The table name.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editRenderSettingButton(\DC_Table $dataContainer, $table)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $event = new GenerateHtmlEvent(
            'alias.gif',
            $GLOBALS['TL_LANG'][$table]['editrendersetting'][0],
            'style="vertical-align:top"'
        );

        $dispatcher = $this->getEventDispatcher();

        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

        $url = BackendUrlBuilder::fromUrl('contao/main.php?do=metamodels&table=tl_metamodel_rendersetting')
            ->setQueryParameter(
                'pid',
                IdSerializer::fromValues('tl_metamodel_rendersettings', $dataContainer->value)->getSerialized()
            );

        return sprintf(
            '<a href="%s" title="%s" style="padding-left:3px">%s</a>',
            $url->getUrl(),
            sprintf(specialchars($GLOBALS['TL_LANG'][$table]['editrendersetting'][1]), $dataContainer->value),
            $event->getHtml()
        );
    }

    /**
     * Fetch all available filter settings for the current meta model.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return string[int] array of all attributes as id => human name
     */
    public function getFilterSettings(\DC_Table $objDC)
    {
        $objDB             = \Database::getInstance();
        $objFilterSettings = $objDB
            ->prepare('SELECT * FROM tl_metamodel_filter WHERE pid=?')
            ->execute($objDC->activeRecord->metamodel);
        $arrSettings       = array();

        while ($objFilterSettings->next()) {
            $arrSettings[$objFilterSettings->id] = $objFilterSettings->name;
        }

        // Sort the filter settings.
        asort($arrSettings);

        return $arrSettings;
    }

    /**
     * Fetch all available render settings for the current meta model.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return string[int] array of all attributes as id => human name
     */
    public function getRenderSettings(\DC_Table $objDC)
    {
        $objFilterSettings = \Database::getInstance()
            ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($objDC->activeRecord->metamodel);

        $arrSettings = array();
        while ($objFilterSettings->next()) {
            $arrSettings[$objFilterSettings->id] = $objFilterSettings->name;
        }

        // Sort the render settings.
        asort($arrSettings);
        return $arrSettings;
    }

    /**
     * Get frontend templates for filters.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getFilterTemplates()
    {
        $list = new TemplateList();
        $list->setServiceContainer($GLOBALS['container']['metamodels-service-container']);

        return $list->getTemplatesForBase('mm_filter_');
    }

    /**
     * Fetch all attribute names for the current MetaModel.
     *
     * @param \DC_Table $objDc The data container calling this method.
     *
     * @return string[string] array of all attributes as colName => human name
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getAttributeNames(\DC_Table $objDc)
    {
        $arrAttributeNames = array(
            'sorting' => $GLOBALS['TL_LANG']['MSC']['sorting'],
            'random'  => $GLOBALS['TL_LANG']['MSC']['random']
        );
        $objMetaModel      = MetaModelFactory::byId($objDc->activeRecord->metamodel);

        if ($objMetaModel) {
            foreach ($objMetaModel->getAttributes() as $objAttribute) {
                $arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName();
            }
        }

        return $arrAttributeNames;
    }

    /**
     * Get a list with all allowed attributes for meta title.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array A list with all found attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaTitleAttributes(\DC_Table $objDC)
    {
        return Helper::getAttributeNamesForModel(
            $objDC->activeRecord->metamodel,
            (array) $GLOBALS['METAMODELS']['metainformation']['allowedTitle']
        );
    }

    /**
     * Get a list with all allowed attributes for meta description.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array A list with all found attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaDescriptionAttributes(\DC_Table $objDC)
    {
        return Helper::getAttributeNamesForModel(
            $objDC->activeRecord->metamodel,
            (array) $GLOBALS['METAMODELS']['metainformation']['allowedDescription']
        );
    }

    /**
     * Get attributes for checkbox wizard.
     *
     * @param \DC_Table $objDc The current row.
     *
     * @return array
     */
    public function getFilterParameterNames(\DC_Table $objDc)
    {
        $return = array();

        if (!$objDc->activeRecord->metamodel_filtering) {
            return $return;
        }

        $objFilterSetting = FilterFactory::byId($objDc->activeRecord->metamodel_filtering);
        $arrParameterDca  = $objFilterSetting->getParameterFilterNames();

        return $arrParameterDca;
    }
}
