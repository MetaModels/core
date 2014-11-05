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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function buildCustomFilter(\DC_Table $dataContainer)
    {
        parent::buildFilterParams($dataContainer, 'tl_content', 'metamodel_content');
    }

    /**
     * Fetch the template group for the current MetaModel content element.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array
     */
    public function getModuleTemplates(\DC_Table $objDC)
    {
        $type = $objDC->activeRecord->type;
        if ($type == 'metamodel_content') {
            $type = 'metamodel_list';
        }

        return Helper::getTemplatesForBase('ce_' . $type);
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
        return parent::editMetaModel($dataContainer, 'tl_content');
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
        return parent::editFilterSetting($dataContainer, 'tl_content');
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
        return parent::editRenderSetting($dataContainer, 'tl_content');
    }
}
