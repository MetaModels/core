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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Module;

use MetaModels\FrontendIntegration\HybridList;

/**
 * Implementation of the MetaModel content element.
 */
class ModelList extends HybridList
{
    /**
     * The Template instance.
     *
     * @var string
     */
    protected $strTemplate = 'mod_metamodel_list';

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
    protected $typePrefix = 'mod_';
}
