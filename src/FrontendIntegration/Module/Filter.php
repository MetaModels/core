<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Module;

use MetaModels\FrontendIntegration\HybridFilterBlock;

/**
 * Frontend module for FE-filtering.
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
    protected $wildCardLink = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=%s';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix = 'mod_';

    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName = '### METAMODEL FILTER ###';
}
