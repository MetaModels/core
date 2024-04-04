<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Content;

use MetaModels\FrontendIntegration\HybridFilterClearAll;

/**
 * Content element clearing the FE-filter.
 *
 * @psalm-suppress DeprecatedClass
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FilterClearAll extends HybridFilterClearAll
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mm_clearall_default';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink = 'contao/main.php?do=page&amp;table=tl_content&amp;act=edit&amp;id=%s';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix = 'ce_';

    /**
     * The current element type.
     *
     * @var string
     */
    protected $type = 'ce';
}
