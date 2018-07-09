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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Compat;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\InsertTags;

/**
 * This class creates instances of Contao classes that have parent classes in global namespace (All in Contao <=4.4).
 *
 * To instantiate, we ensure the framwork is booted prior usage.
 */
class ContaoFactory
{
    /**
     * The Contao framework.
     *
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Create a new instance.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Create an insert tags replacer.
     *
     * @return InsertTags
     */
    public function createInsertTags()
    {
        $this->framework->initialize();

        return new InsertTags();
    }
}
