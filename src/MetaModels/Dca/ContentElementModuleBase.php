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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Meierhans <s.meierhans@gmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\UrlBuilder\Contao\BackendUrlBuilder;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\IMetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Complementary methods needed by the DCA in tl_module and tl_content.
 */
class ContentElementModuleBase
{
    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getServiceContainer()->getEventDispatcher();
    }
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }

    /**
     * Retrieve the database instance.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return $this->getServiceContainer()->getDatabase();
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
        $container = $this->getServiceContainer();
        $element   = $this->getDatabase()
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

        $objFilterSettings = $container->getFilterFactory()->createCollection(
            $element->metamodel_filtering
        );

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
            ->setQueryParameter('id', ModelId::fromValues('tl_metamodel', $dataContainer->value)->getSerialized());

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
                ModelId::fromValues('tl_metamodel_filter', $dataContainer->value)->getSerialized()
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
                ModelId::fromValues('tl_metamodel_rendersettings', $dataContainer->value)->getSerialized()
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
     * @return string[] array of all attributes as id => human name
     */
    public function getFilterSettings(\DC_Table $objDC)
    {
        $objFilterSettings = $this
            ->getDatabase()
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
     * @return string[] array of all attributes as id => human name
     */
    public function getRenderSettings(\DC_Table $objDC)
    {
        $objFilterSettings = $this
            ->getDatabase()
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
     * @return string[] array of all attributes as colName => human name
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getAttributeNames(\DC_Table $objDc)
    {
        $arrAttributeNames = array(
            'sorting' => $GLOBALS['TL_LANG']['MSC']['sorting'],
            'random'  => $GLOBALS['TL_LANG']['MSC']['random'],
            'id'      => $GLOBALS['TL_LANG']['MSC']['id'][0]
        );

        $factory       = $this->getServiceContainer()->getFactory();
        $metaModelName = $factory->translateIdToMetaModelName($objDc->activeRecord->metamodel);
        $objMetaModel  = $factory->getMetaModel($metaModelName);

        if ($objMetaModel) {
            foreach ($objMetaModel->getAttributes() as $objAttribute) {
                $arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName();
            }
        }

        return $arrAttributeNames;
    }

    /**
     * Get a list with all allowed attributes for meta description.
     *
     * If the optional parameter arrTypes is not given, all attributes will be retrieved.
     *
     * @param int      $metaModelId  The id of the MetaModel from which the attributes shall be retrieved from.
     *
     * @param string[] $allowedTypes The attribute type names that shall be retrieved (optional).
     *
     * @return array A list with all found attributes.
     */
    public function getAttributeNamesForModel($metaModelId, $allowedTypes = array())
    {
        $attributeNames = array();

        $factory   = $this->getServiceContainer()->getFactory();
        $metaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($metaModelId));
        if ($metaModel) {
            foreach ($metaModel->getAttributes() as $attribute) {
                if (empty($allowedTypes) || in_array($attribute->get('type'), $allowedTypes)) {
                    $attributeNames[$attribute->getColName()] =
                        sprintf(
                            '%s [%s]',
                            $attribute->getName(),
                            $attribute->getColName()
                        );
                }
            }
        }

        return $attributeNames;
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
        return $this->getAttributeNamesForModel(
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
        return $this->getAttributeNamesForModel(
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
        $filter = $objDc->activeRecord->metamodel_filtering;

        if (!$filter) {
            return $return;
        }

        $objFilterSetting = $this->getServiceContainer()->getFilterFactory()->createCollection($filter);
        $arrParameterDca  = $objFilterSetting->getParameterFilterNames();

        return $arrParameterDca;
    }
}
