<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged in user (either frontend or backend).
 *
 * @package MetaModels
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
        if (strpos(\Environment::get('script'), 'contao/index.php') !== false) {
            return false;
        }

        // Issue #66 - contao/install.php is not working anymore. Thanks to Stefan Lindecke (@lindesbs).
        if (strpos(\Environment::get('request'), 'install.php') !== false) {
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
