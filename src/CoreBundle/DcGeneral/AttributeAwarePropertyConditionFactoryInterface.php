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
 * This interface describes a property condition factory that uses an attribute.
 */
interface AttributeAwarePropertyConditionFactoryInterface extends PropertyConditionFactoryInterface
{
    /**
     * Test if an attribute type is supported.
     *
     * @param string $attribute The attribute type name.
     *
     * @return bool
     */
    public function supportsAttribute($attribute);
}
