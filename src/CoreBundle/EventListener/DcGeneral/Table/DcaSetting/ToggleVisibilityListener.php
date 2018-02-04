<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use Doctrine\Common\Cache\Cache;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class listen the toggle event.
 */
class ToggleVisibilityListener extends AbstractAbstainingListener
{
    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * The cache.
     *
     * @var Cache
     */
    private $cache;

    /**
     * The authentication resolver.
     *
     * @var AuthenticationTrustResolverInterface
     */
    private $authenticationTrustResolver;

    /**
     * ToggleVisibilityListener constructor.
     *
     * @param RequestScopeDeterminator             $scopeDeterminator           The scope determinator.
     *
     * @param TokenStorageInterface                $tokenStorage                The token storage.
     *
     * @param ViewCombination                      $viewCombination             The view combination.
     *
     * @param Cache                                $cache                       The cache.
     *
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver The authentication resolver.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        TokenStorageInterface $tokenStorage,
        ViewCombination $viewCombination,
        Cache $cache,
        AuthenticationTrustResolverInterface $authenticationTrustResolver
    ) {
        parent::__construct($scopeDeterminator);

        $this->tokenStorage                = $tokenStorage;
        $this->viewCombination             = $viewCombination;
        $this->cache                       = $cache;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }

    /**
     * This rebuilds the screen cache when the visibility of a property is changed.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    public function handle(ActionEvent $event)
    {
        if (!$this->wantToHandle($event)
            || 'toggle' !== $event->getAction()->getName()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || $this->authenticationTrustResolver->isAnonymous($token)) {
            return;
        }

        $combinations = $this->viewCombination->getCombinations();
        $screenIds    = array_map(
            function ($combination) {
                return $combination['dca_id'];
            },
            $combinations['byName']
        );
        if ($this->cache->contains($cacheKey = 'screens_' . implode(',', $screenIds))) {
            $this->cache->delete($cacheKey);
        }

        $this->viewCombination->getScreen(null);
    }
}
