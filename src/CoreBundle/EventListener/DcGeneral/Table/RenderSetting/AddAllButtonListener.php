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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This fixes the href on the "add-all" button in input screens.
 */
class AddAllButtonListener
{
    /**
     * The database.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The URL generator.
     *
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * Create a new instance.
     *
     * @param Connection            $connection   The connection.
     * @param IFactory              $factory      The factory.
     * @param UrlGeneratorInterface $urlGenerator The url generator.
     * @param RequestStack          $requestStack The session.
     */
    public function __construct(
        Connection $connection,
        IFactory $factory,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack
    ) {
        $this->connection = $connection;
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * Clear the button if the User is not admin.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getGlobalButton(GetGlobalButtonEvent $event)
    {
        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            'addall' !== $event->getKey()
            || 'tl_metamodel_rendersetting' !== $dataDefinition->getName()
        ) {
            return;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $renderSetting = ModelId::fromSerialized($inputProvider->getParameter('pid'))->getId();

        $modelId = $this->connection->createQueryBuilder()
            ->select('r.pid')
            ->from('tl_metamodel_rendersettings', 'r')
            ->where('r.id=:pid')
            ->setParameter('pid', $renderSetting)
            ->executeQuery()
            ->fetchOne();

        if (false === $modelId) {
            return;
        }

        $name    = $this->factory->translateIdToMetaModelName($modelId);
        $request = $this->requestStack->getCurrentRequest();

        $event->setHref(
            $this->urlGenerator->generate(
                'metamodels.rendersetting.add_all',
                ['metaModel' => $name, 'renderSetting' => $renderSetting]
            )
            . '?ref=' . $request?->attributes->get('_contao_referer_id')
        );
    }
}
