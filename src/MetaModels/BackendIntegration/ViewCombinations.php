<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use Contao\Environment;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged in user (either frontend or backend).
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
        // Do not execute anything if we are on the index page because no User is logged in.
        if (strpos(Environment::get('script'), 'contao/index.php') !== false) {
            return false;
        }

        // Issue #66 - contao/install.php is not working anymore. Thanks to Stefan Lindecke (@lindesbs).
        if (strpos(Environment::get('request'), 'install.php') !== false) {
            return false;
        }

        if (strpos(Environment::get('script'), 'system/bin') !== false) {
            return false;
        }

        // Bug fix: If the user is not authenticated, contao will redirect to contao/index.php
        // But in this moment the TL_PATH is not defined, so the $this->Environment->request
        // generate a url without replacing the basepath(TL_PATH) with an empty string.
        $authResult = $this->getUser()->authenticate();

        return ($authResult === true || $authResult === null) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserGroups()
    {
        // Try to get the group(s)
        // there might be a NULL in there as BE admins have no groups and user might have one but it is not mandatory.
        // I would prefer a default group for both, fe and be groups.
        $groups = parent::getUserGroups();

        /** @noinspection PhpUndefinedFieldInspection */
        // Special case in combinations, admins have the implicit group id -1.
        if ($this->getUser()->admin) {
            $groups[] = -1;
        }

        return $groups;
    }
}
