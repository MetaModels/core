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

namespace MetaModels\CoreBundle\DcGeneral;

/**
 * This is the abstract base for attribute aware condition factories.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class AbstractRestrictedAttributeConditionFactory extends AbstractAttributeConditionFactory
{
    /**
     * The list of supported attribute types.
     *
     * @var array
     */
    private $supportedAttributeTypes;

    /**
     * Create a new instance.
     *
     * @param array $supportedAttributeTypes
     */
    public function __construct(array $supportedAttributeTypes)
    {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return \in_array($attribute, $this->supportedAttributeTypes, true);
    }
}
