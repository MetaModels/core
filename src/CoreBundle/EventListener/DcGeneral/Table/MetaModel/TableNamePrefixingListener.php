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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\Helper\TableManipulator;

/**
 * This prefixes all tables with "mm_".
 */
class TableNamePrefixingListener extends AbstractAbstainingListener
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
     * Called by tl_metamodel.tableName onsave_callback.
     *
     * Prefixes the table name with mm_ if not provided by the user as such.
     * Checks if the table name is legal to the DB.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException On invalid table names.
     */
    public function handle(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'tableName')) {
            return;
        }

        // See #49.
        $tableName = strtolower($event->getValue());

        if (!strlen($tableName)) {
            throw new \RuntimeException('Table name not given');
        }

        // Force mm_ prefix.
        if (substr($tableName, 0, 3) !== 'mm_') {
            $tableName = 'mm_' . $tableName;
        }

        $dataProvider = $event->getEnvironment()->getDataProvider('tl_metamodel');

        try {
            // New model, ensure the table does not exist.
            if (!$event->getModel()->getId()) {
                $this->tableManipulator->checkTableDoesNotExist($tableName);
            } else {
                // Edited model, ensure the value is unique and then that the table does not exist.
                $oldVersion = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($event->getModel()->getId()));

                if ($oldVersion->getProperty('tableName') !== $event->getModel()->getProperty('tableName')) {
                    $this->tableManipulator->checkTableDoesNotExist($tableName);
                }
            }
        } catch (\Exception $exception) {
            throw new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $event->setValue($tableName);
    }
}
