<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Marc Reimann <reimann@mediendepot-ruhr.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\DC_Table;

/**
 * This class provides callbacks for tl_content.
 */
class ContentElementCallback extends AbstractContentElementAndModuleCallback
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $tableName = 'tl_content';

    /**
     * Called from tl_content.onload_callback.
     *
     * @param DC_Table $dataContainer The data container calling this method.
     *
     * @return void
     */
    public function buildFilterParameterList(DC_Table $dataContainer)
    {
        parent::buildFilterParamsFor($dataContainer, 'metamodel_content');
    }

    /**
     * Fetch the template group for the current MetaModel content element.
     *
     * @param DC_Table $objDC The data container calling this method.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getTemplates(DC_Table $objDC)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $type = $objDC->activeRecord->type;
        if ($type == 'metamodel_content') {
            $type = 'metamodel_list';
        }

        return $this->getTemplateList('ce_' . $type);
    }

    /**
     * Fetch the template group for the current MetaModel content element.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getPaginationTemplates()
    {
        return $this->getTemplateList('mm_pagination');
    }
}
