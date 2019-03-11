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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * This adds a method "ids" for being able to inspect the list of registered service ids to the service locator.
 *
 * This helps mainly for debug purposes and for enumerating the services at the expense of more memory usage.
 */
class IdProvidingServiceLocator extends ServiceLocator
{
    /**
     * The id list.
     *
     * @var array
     */
    private $serviceIds;

    /**
     * Create a new instance.
     *
     * @param \Closure[] $factories The factories.
     */
    public function __construct($factories)
    {
        parent::__construct($factories);
        $this->serviceIds = \array_keys($factories);
    }

    /**
     * Obtain the ids of registered services.
     *
     * @return string[]
     */
    public function ids(): array
    {
        return $this->serviceIds;
    }
}
