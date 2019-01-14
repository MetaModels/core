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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged in user (either frontend or backend).
 *
 * @deprecated This will get removed.
 */
class ViewCombinations extends \MetaModels\Helper\ViewCombinations
{
    /**
     * Authenticate the user preserving the object stack.
     *
     * @return bool
     */
    protected function authenticateUser()
    {
        return $this->getUser()->authenticate();
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserGroups()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        // Special case in combinations, anonymous frontend users have the implicit group id -1.
        if (!$this->getUser()->id) {
            return [-1];
        }

        return parent::getUserGroups();
    }
}
