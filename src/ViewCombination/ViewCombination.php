<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ViewCombination;

use Contao\BackendUser;
use Contao\FrontendUser;
use Doctrine\Common\Cache\Cache;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class serves as central information endpoint for view combinations.
 */
class ViewCombination
{
    /**
     * The cache.
     *
     * @var Cache
     */
    private $cache;

    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ViewCombinationBuilder
     */
    private $builder;

    /**
     * @var InputScreenInformationBuilder
     */
    private $inputScreens;

    /**
     * Create a new instance.
     *
     * @param Cache                         $cache        The cache.
     * @param TokenStorageInterface         $tokenStorage The token storage.
     * @param ViewCombinationBuilder        $builder      The combination builder.
     * @param InputScreenInformationBuilder $inputScreens The input screen information builder.
     */
    public function __construct(
        Cache $cache,
        TokenStorageInterface $tokenStorage,
        ViewCombinationBuilder $builder,
        InputScreenInformationBuilder $inputScreens
    ) {
        $this->cache        = $cache;
        $this->tokenStorage = $tokenStorage;
        $this->builder      = $builder;
        $this->inputScreens = $inputScreens;
    }

    /**
     * Obtain the combinations for the current user.
     *
     * @return array
     */
    public function getCombinations()
    {
        $user = $this->getUser();

        switch (true) {
            case ($user instanceof BackendUser):
                $mode = 'be';
                // Try to get the group(s)
                // there might be a NULL in there as BE admins have no groups and
                // user might have one but it is not mandatory.
                // I would prefer a default group for both, fe and be groups.
                $groups = $user->groups;
                // Special case in combinations, admins have the implicit group id -1.
                if ($user->admin) {
                    $groups[] = -1;
                }

                break;
            case ($user instanceof FrontendUser):
                $mode = 'fe';
                $groups = $user->groups;
                // Special case in combinations, anonymous frontend users have the implicit group id -1.
                if (!$this->getUser()->id) {
                    $groups = [-1];
                }

                break;
            default:
                // Default handled as frontend anonymous.
                $mode = 'fe';
                $groups = [-1];
        }

        $groups = array_filter($groups);

        if ($this->cache->contains($cacheKey = 'combinations_' . $mode . '_' . implode(',', $groups))) {
            return $this->cache->fetch($cacheKey);
        }

        $combinations = $this->builder->getCombinationsForUser($groups, $mode);

        $this->cache->save($cacheKey, $combinations);

        return $combinations;
    }

    /**
     * Retrieve a combination for a table.
     *
     * @param string $tableName The table name.
     *
     * @return array|null
     */
    public function getCombination($tableName)
    {
        $combinations = $this->getCombinations();
        if (isset($combinations['byName'][$tableName])) {
            return $combinations['byName'][$tableName];
        }

        return null;
    }

    /**
     * Obtain stand alone input screens.
     *
     * @return array
     */
    public function getStandalone()
    {
        $inputScreens = array_filter($this->getInputScreens(), function ($inputScreen) {
            return $inputScreen['meta']['rendertype'] === 'standalone';
        });

        return $inputScreens;
    }

    /**
     * Obtain parented input screens.
     *
     * @return array
     */
    public function getParented()
    {
        $inputScreens = array_filter($this->getInputScreens(), function ($inputScreen) {
            return $inputScreen['meta']['rendertype'] === 'ctable';
        });

        return $inputScreens;
    }

    /**
     * Obtain child input screens of the passed parent.
     *
     * @param string $parentTable The parent table to assemble the children of.
     *
     * @return array
     */
    public function getChildrenOf($parentTable)
    {
        $inputScreens = array_filter($this->getInputScreens(), function ($inputScreen) use ($parentTable){
            return ($inputScreen['meta']['rendertype'] === 'ctable') && ($inputScreen['meta']['ptable'] === $parentTable);
        });

        return $inputScreens;
    }

    /**
     * Obtain parented input screens.
     *
     * @param string $tableName The table name.
     *
     * @return array|null
     */
    public function getScreen($tableName)
    {
        $inputScreens = $this->getInputScreens();
        if (isset($inputScreens[$tableName])) {
            return $inputScreens[$tableName];
        }

        return null;
    }

    /**
     *
     *
     * @return array
     */
    private function getInputScreens()
    {
        $combinations = $this->getCombinations();
        $screenIds    = array_map(function ($combination) {
            return $combination['dca_id'];
        }, $combinations['byName']);

        if ($this->cache->contains($cacheKey = 'screens_' . implode(',', $screenIds))) {
            return $this->cache->fetch($cacheKey);
        }

        $screens = $this->inputScreens->fetchInputScreens($screenIds);
        $this->cache->save($cacheKey, $screens);

        return $screens;
    }

    /**
     * The user.
     *
     * @return mixed
     */
    private function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }
        return $token->getUser();
    }
}
