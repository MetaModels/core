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

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DC_Table;

/**
 * This class provides callbacks for tl_content.
 */
final class FilterContentElementCallback extends AbstractContentElementAndModuleCallback
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $tableName = 'tl_content';

    /** Called from tl_content.onload_callback. */
    public function buildFilterParameterList(DC_Table $dataContainer): void
    {
        try {
            if (
                null === ($currentRecord = $dataContainer->getCurrentRecord())
                || $currentRecord['type'] !== 'metamodels_frontendfilter'
            ) {
                return;
            }
        } catch (AccessDeniedException $exception) {
            // If the access is denied, we don't want to build filter parameters.
            return;
        }
        $this->buildFilterParamsFor($dataContainer, 'metamodels_frontendfilter');
    }
}
