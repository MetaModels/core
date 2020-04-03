<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
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
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaCombine;

use Doctrine\DBAL\Connection;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;

/**
 * This class handles obtaining the render setting options.
 */
class RenderSettingOptionListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get all options for the input screens.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOptionsEvent $event)
    {
        if (('tl_metamodel_dca_combine' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('rows' !== $event->getPropertyName())
            || 'view_id' !== $event->getSubPropertyName()) {
            return;
        }

        $screens = $this
            ->connection
            ->createQueryBuilder()
            ->select('`id`')
            ->addSelect('`name`')
            ->from('tl_metamodel_rendersettings')
            ->where('pid=:pid')
            ->setParameter('pid', $event->getModel()->getProperty('id'))
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($screens as $screen) {
            $result[$screen['id']] = $screen['name'];
        }

        $event->setOptions($result);
    }
}
