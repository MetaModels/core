<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Content;

use MetaModels\FrontendIntegration\HybridFilterBlock;

/**
 * Content element for FE-filtering.
 */
class Filter extends HybridFilterBlock
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mm_filter_default';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink = 'contao/main.php?do=metamodels';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix = 'ce_';
}
