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

use MetaModels\IMetaModel;

/**
 * This is the abstract base for attribute aware condition factories.
 */
abstract class AbstractAttributeConditionFactory implements AttributeAwarePropertyConditionFactoryInterface
{
    /**
     * Extract the attribute instance from the MetaModel.
     *
     * @param IMetaModel $metaModel   The MetaModel instance.
     *
     * @param string     $attributeId The attribute id.
     *
     * @return string
     *
     * @throws \RuntimeException When the attribute could not be retrieved.
     */
    protected function attributeIdToName(IMetaModel $metaModel, $attributeId)
    {
        if (null === $attribute = $metaModel->getAttributeById($attributeId)) {
            throw new \RuntimeException(sprintf(
                'Could not retrieve attribute %s from MetaModel %s.',
                $attributeId,
                $metaModel->getTableName()
            ));
        }

        return $attribute->getColName();
    }
}
