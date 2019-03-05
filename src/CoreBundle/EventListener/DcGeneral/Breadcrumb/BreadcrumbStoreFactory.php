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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use MetaModels\CoreBundle\Assets\IconBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class creates an instance of a breadcrumb store.
 */
class BreadcrumbStoreFactory
{
    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Create a new instance.
     *
     * @param IconBuilder         $iconBuilder  The icon builder.
     * @param TranslatorInterface $translator   The translator.
     * @param RequestStack        $requestStack The request stack.
     */
    public function __construct(IconBuilder $iconBuilder, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->iconBuilder  = $iconBuilder;
        $this->translator   = $translator;
        $this->requestStack = $requestStack;
    }

    /**
     * Create a breadcrumb store.
     *
     * @return BreadcrumbStore
     */
    public function createStore()
    {
        $request = $this->requestStack->getCurrentRequest();

        return new BreadcrumbStore($this->iconBuilder, $this->translator, $request ? $request->getUri() : '');
    }
}
