<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DataProvider;

/**
 * Data provider for tl_metamodel_dcasetting that handles virtual panel properties.
 *
 * Resolves the MetaModel ID via tl_metamodel_dca.pid.
 */
final class DcaSettingAttrTypeDataProvider extends AbstractAttrTypeDataProvider
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    protected function getMetaModelIdFromParentId(int $parentId): ?int
    {
        $result = $this->connection
            ->createQueryBuilder()
            ->select('pid')
            ->from('tl_metamodel_dca')
            ->where('id = :id')
            ->setParameter('id', $parentId)
            ->executeQuery()
            ->fetchOne();

        return false === $result ? null : (int) $result;
    }
}
