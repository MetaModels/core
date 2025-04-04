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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     binron <rtb@gmx.ch>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Compat;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\InsertTags;

/**
 * This class creates instances of Contao classes that have parent classes in global namespace (All in Contao <=4.4).
 *
 * To instantiate, we ensure the framework is booted prior usage.
 */
class ContaoFactory
{
    /**
     * The Contao framework.
     *
     * @var ContaoFramework
     *
     * @psalm-suppress DeprecatedInterface
     */
    private ContaoFramework $framework;

    /**
     * Create a new instance.
     *
     * @param ContaoFramework $framework The Contao framework.
     */
    public function __construct(ContaoFramework $framework)
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
        /** @psalm-suppress InternalMethod - the ContaoFramework class is internal, not the method usage. */
        $this->framework->initialize();

        return new InsertTags();
    }

    /**
     * Create an adapter.
     *
     * @param class-string $className The class name to create an adapter for.
     *
     * @return Adapter
     */
    public function getAdapter($className)
    {
        /** @psalm-suppress InternalMethod - the ContaoFramework class is internal, not the method usage. */
        $this->framework->initialize();

        /** @psalm-suppress InternalMethod - the ContaoFramework class is internal, not the method usage. */
        return $this->framework->getAdapter($className);
    }

    /**
     * Create an instance.
     *
     * @param class-string $className The class name to create an instance for.
     *
     * @return object
     */
    public function createInstance($className)
    {
        /** @psalm-suppress InternalMethod - the ContaoFramework class is internal, not the method usage. */
        $this->framework->initialize();

        /** @psalm-suppress InternalMethod - the ContaoFramework class is internal, not the method usage. */
        return $this->framework->createInstance($className);
    }
}
