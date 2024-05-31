<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use Contao\Environment;
use Contao\System;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged-in user (either frontend or backend).
 *
 * @deprecated This will get removed.
 *
 * @psalm-suppress DeprecatedClass
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
        $scopeMatcher = System::getContainer()->get('cca.dc-general.scope-matcher');
        if (null === $scopeMatcher || $scopeMatcher->currentScopeIsUnknown()) {
            return false;
        }

        // Do not execute anything if we are on the login page because no User is logged in.
        if (\str_contains(Environment::get('script'), 'contao/login')) {
            return false;
        }

        // Issue #66 - contao/install.php is not working anymore. Thanks to Stefan Lindecke (@lindesbs).
        if (\str_contains(Environment::get('request'), 'install')) {
            return false;
        }

        if (\str_contains(Environment::get('script'), 'system/bin')) {
            return false;
        }

        // Bug fix: If the user is not authenticated, contao will redirect to contao/index.php
        // But at this moment the TL_PATH is not defined, so the $this->Environment->request
        // generate an url without replacing the basepath(TL_PATH) with an empty string.
        /** @psalm-suppress DeprecatedMethod */
        return $this->getUser()->authenticate();
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserGroups()
    {
        // Try to get the group(s)
        // there might be a NULL in there as BE admins have no groups and user might have one, but it is not mandatory.
        // I would prefer a default group for both, fe and be groups.
        /** @psalm-suppress DeprecatedClass */
        $groups = parent::getUserGroups();

        /** @noinspection PhpUndefinedFieldInspection */
        // Special case in combinations, admins have the implicit group id -1.
        if ($this->getUser()->admin) {
            $groups[] = -1;
        }

        return $groups;
    }
}
