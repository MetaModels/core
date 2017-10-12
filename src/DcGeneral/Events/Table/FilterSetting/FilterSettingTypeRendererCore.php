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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
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
