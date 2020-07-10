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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Contao\InsertTag;

/**
 * This insert tag replace the table name in the given content.
 */
final class ReplaceTableName
{
    /**
     * Replace the table name in the given content.
     *
     * @param string $tableName The table name.
     * @param string $content   The content.
     *
     * @return string
     */
    public function replace(string $tableName, string $content): string
    {
        return \str_replace('{{table}}', $tableName, $content);
    }
}
