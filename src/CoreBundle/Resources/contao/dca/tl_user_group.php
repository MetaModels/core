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

use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;

$prefCallback = $GLOBALS['TL_DCA']['tl_user_group']['fields']['alexf']['options_callback'] ?? null;
// Filter all MetaModels tables from user group permissions - only Admins MUST edit MetaModels.
$GLOBALS['TL_DCA']['tl_user_group']['fields']['alexf']['options_callback'] =
static function () use ($prefCallback): array {
    $options = (null === $prefCallback) ? [] : Callbacks::call($prefCallback);
    foreach (\array_keys($options) as $tableName) {
        if (str_starts_with($tableName, 'tl_metamodel')) {
            unset($options[$tableName]);
        }
    }

    return $options;
};
