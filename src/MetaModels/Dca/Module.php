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
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use MetaModels\BackendIntegration\TemplateList;

/**
 * Complementary methods needed by the DCA in tl_module.
 */
class Module extends ContentElementModuleBase
{
    /**
     * Called from tl_module.onload_callback.
     *
     * @param \DC_Table $dataContainer The data container calling this method.
     *
     * @return void
     */
    public function buildFilterParams(\DC_Table $dataContainer)
    {
        parent::buildFilterParamsFor($dataContainer, 'tl_module', 'metamodel_list');
    }

    /**
     * Fetch the template group for the current MetaModel module.
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
        $list = new TemplateList();
        $list->setServiceContainer($GLOBALS['container']['metamodels-service-container']);

        return $list->getTemplatesForBase('mod_' . $type);
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
        return parent::editMetaModelButton($dataContainer, 'tl_module');
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
        return parent::editFilterSettingButton($dataContainer, 'tl_module');
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
        return parent::editRenderSettingButton($dataContainer, 'tl_module');
    }
}
