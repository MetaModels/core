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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Handles rendering of model from tl_metamodel_filtersetting.
 */
class FilterSettingTypeRendererCore extends FilterSettingTypeRenderer
{
    /**
     * Retrieve the types this renderer is valid for.
     *
     * @return array
     */
    protected function getTypes()
    {
        return array('idlist', 'simplelookup', 'customsql', 'conditionand', 'conditionor');
    }

    /**
     * Retrieve the parameters for the label.
     *
     * @param EnvironmentInterface $environment The translator in use.
     *
     * @param ModelInterface       $model       The model.
     *
     * @return array
     */
    protected function getLabelParameters(EnvironmentInterface $environment, ModelInterface $model)
    {
        if ($model->getProperty('type') == 'simplelookup') {
            return $this->getLabelParametersWithAttributeAndUrlParam($environment, $model);
        }
        return $this->getLabelParametersNormal($environment, $model);
    }
}
