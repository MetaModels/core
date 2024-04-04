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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
    private Cache $cache;

    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * The combination builder.
     *
     * @var ViewCombinationBuilder
     */
    private ViewCombinationBuilder $builder;

    /**
     * The input screen information builder.
     *
     * @var InputScreenInformationBuilder
     */
    private InputScreenInformationBuilder $inputScreens;

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
     * @return array|null
     */
    public function getCombinations()
    {
        $user = $this->getUser();

        switch (true) {
            case ($user instanceof BackendUser):
                $mode = 'be';
                // Try to get the group(s)
                // there might be a NULL in there as BE admins have no groups and
                // user might have one, but it is not mandatory.
                // I would prefer a default group for both, fe and be groups.
                $groups = $user->groups;
                // Special case in combinations, admins have the implicit group id -1.
                if ((bool) $user->admin) {
                    $groups[] = -1;
                }

                break;
            case ($user instanceof FrontendUser):
                $mode   = 'fe';
                $groups = $user->groups;
                // Special case in combinations, anonymous frontend users have the implicit group id -1.
                if (!$this->getUser()->id) {
                    $groups = [-1];
                }

                break;
            default:
                // Default handled as frontend anonymous.
                $mode   = 'fe';
                $groups = [-1];
        }

        $groups = \array_filter($groups);

        if ($this->cache->contains($cacheKey = 'combinations_' . $mode . '_' . \implode(',', $groups))) {
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

        return $combinations['byName'][$tableName] ?? null;
    }

    /**
     * Obtain stand-alone input screens.
     *
     * @return array
     */
    public function getStandalone()
    {
        $inputScreens = \array_filter($this->getInputScreens(), static function ($inputScreen) {
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
        $inputScreens = \array_filter($this->getInputScreens(), static function ($inputScreen) {
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
        $inputScreens = \array_filter($this->getInputScreens(), static function ($inputScreen) use ($parentTable) {
            return ($inputScreen['meta']['rendertype'] === 'ctable')
                   && ($inputScreen['meta']['ptable'] === $parentTable);
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

        return $inputScreens[$tableName] ?? null;
    }

    /**
     * Retrieve the input screens.
     *
     * @return array
     */
    private function getInputScreens()
    {
        $combinations = $this->getCombinations();
        if (null === $combinations) {
            return [];
        }

        $screenIds = \array_map(static function (array $combination): mixed {
            return $combination['dca_id'];
        }, $combinations['byName']);

        if ($this->cache->contains($cacheKey = 'screens_' . \implode(',', $screenIds))) {
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
