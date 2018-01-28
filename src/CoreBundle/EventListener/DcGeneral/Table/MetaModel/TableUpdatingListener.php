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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use MetaModels\Helper\TableManipulator;

/**
 * This handles table renaming and table deleting.
 */
class TableUpdatingListener extends AbstractAbstainingListener
{
    /**
     * The table manipulator.
     *
     * @var TableManipulator
     */
    private $tableManipulator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param TableManipulator         $tableManipulator  The table manipulator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, TableManipulator $tableManipulator)
    {
        parent::__construct($scopeDeterminator);
        $this->tableManipulator = $tableManipulator;
    }

    /**
     * Handle the deletion of a MetaModel and all attached data.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function handleDelete(PreDeleteModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        try {
            $this->tableManipulator->checkTableExists($tableName = $event->getModel()->getProperty('tableName'));
        } catch (\Exception $exception) {
            // Exit if table does not exist.
            return;
        }

        $this->tableManipulator->deleteTable($tableName);
    }

    /**
     * Handle the update of a MetaModel and all attached data.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function handleUpdate(PostPersistModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $old      = $event->getOriginalModel();
        $new      = $event->getModel();
        $oldTable = $old ? $old->getProperty('tableName') : null;
        $newTable = $new->getProperty('tableName');

        // Table name changed?
        if ($oldTable !== $newTable) {
            if (!empty($oldTable)) {
                $this->tableManipulator->renameTable($oldTable, $newTable);
                // TODO: notify attributes that the MetaModel has changed its table name.
            } else {
                $this->tableManipulator->createTable($newTable);
            }
        }

        $this->tableManipulator->setVariantSupport($newTable, $new->getProperty('varsupport'));
    }
}
