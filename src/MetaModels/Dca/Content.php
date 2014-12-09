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

use MetaModels\BackendIntegration\TemplateList;

/**
 * Complementary methods needed by the DCA in tl_content.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
class Content extends ContentElementModuleBase
{
    /**
     * Called from tl_content.onload_callback.
     *
     * @param \DC_Table $dataContainer The data container calling this method.
     *
     * @return void
     */
    public function buildCustomFilter(\DC_Table $dataContainer)
    {
        parent::buildFilterParamsFor($dataContainer, 'tl_content', 'metamodel_content');
    }

    /**
     * Fetch the template group for the current MetaModel content element.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getModuleTemplates(\DC_Table $objDC)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $type = $objDC->activeRecord->type;
        if ($type == 'metamodel_content') {
            $type = 'metamodel_list';
        }

        $list = new TemplateList();
        $list->setServiceContainer($GLOBALS['container']['metamodels-service-container']);

        return $list->getTemplatesForBase('ce_' . $type);
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @return string
     */
    public function editMetaModel(\DC_Table $dataContainer)
    {
        return parent::editMetaModelButton($dataContainer, 'tl_content');
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @return string
     */
    public function editFilterSetting(\DC_Table $dataContainer)
    {
        return parent::editFilterSettingButton($dataContainer, 'tl_content');
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @return string
     */
    public function editRenderSetting(\DC_Table $dataContainer)
    {
        return parent::editRenderSettingButton($dataContainer, 'tl_content');
    }
}
