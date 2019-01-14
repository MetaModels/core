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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
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
    private $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The URL generator.
     *
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Create a new instance.
     *
     * @param Connection            $connection   The connection.
     * @param IFactory              $factory      The factory.
     * @param UrlGeneratorInterface $urlGenerator The url generator.
     */
    public function __construct(Connection $connection, IFactory $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->connection   = $connection;
        $this->factory      = $factory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Clear the button if the User is not admin.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     */
    public function getGlobalButton(GetGlobalButtonEvent $event)
    {
        $environment = $event->getEnvironment();
        if ('addall' !== $event->getKey()
        || 'tl_metamodel_rendersetting' !== $environment->getDataDefinition()->getName()) {
            return;
        }

        $renderSetting = ModelId::fromSerialized($environment->getInputProvider()->getParameter('pid'))->getId();

        $modelId = $this->connection->createQueryBuilder()
            ->select('r.pid')
            ->from('tl_metamodel_rendersettings', 'r')
            ->where('r.id=:pid')
            ->setParameter('pid', $renderSetting)
            ->execute()
            ->fetchColumn();

        $name = $this->factory->translateIdToMetaModelName($modelId);

        $event->setHref(
            $this->urlGenerator->generate(
                'metamodels.rendersetting.add_all',
                ['metaModel' => $name, 'renderSetting' => $renderSetting]
            )
        );
    }
}
