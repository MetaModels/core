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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

/**
 * This class provides callbacks for tl_module.
 */
class ModuleCallback extends AbstractContentElementAndModuleCallback
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $tableName = 'tl_module';

    /**
     * Called from tl_content.onload_callback.
     *
     * @param \DC_Table $dataContainer The data container calling this method.
     *
     * @return void
     */
    public function buildFilterParameterList(\DC_Table $dataContainer)
    {
        parent::buildFilterParamsFor($dataContainer, 'metamodel_list');
    }

    /**
     * Fetch the template group for the current MetaModel content element.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array
     */
    public function getTemplates(\DC_Table $objDC)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $type = $objDC->activeRecord->type;

        return $this->getTemplateList('mod_' . $type);
    }
}
