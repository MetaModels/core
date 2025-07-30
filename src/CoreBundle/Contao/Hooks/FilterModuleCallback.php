<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\DC_Table;
use MetaModels\CoreBundle\Contao\Hooks\AbstractContentElementAndModuleCallback;

/**
 * This class provides callbacks for tl_module.
 */
class FilterModuleCallback extends AbstractContentElementAndModuleCallback
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $tableName = 'tl_module';

    /** Called from tl_module.onload_callback. */
    public function buildFilterParameterList(DC_Table $dataContainer): void
    {
        if ($dataContainer->getCurrentRecord()['type'] !== 'metamodels_frontendfilter') {
            return;
        }

        $this->buildFilterParamsFor($dataContainer, 'metamodels_frontendfilter');
    }
}
