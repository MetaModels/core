<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Formatter;

use MetaModels\Attribute\IAttribute;

/**
 * This formats the label for use in the backend as select options in attribute selection lists.
 */
final class SelectAttributeOptionLabelFormatter
{
    /**
     * Format the label.
     *
     * @param IAttribute $attribute The attribute.
     *
     * @return string
     */
    public function formatLabel(IAttribute $attribute): string
    {
        return \sprintf(
            '%1$s [%2$s, "%3$s"]',
            $attribute->getName(),
            $attribute->get('type'),
            $attribute->getColName()
        );
    }
}
