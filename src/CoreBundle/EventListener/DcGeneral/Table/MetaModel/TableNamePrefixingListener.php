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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\Exceptions\Database\InvalidTableNameException;
use MetaModels\Helper\TableManipulator;
use MetaModels\IFactory;

/**
 * This prefixes all tables with "mm_" and check if exists.
 */
class TableNamePrefixingListener extends AbstractAbstainingListener
{
    /**
     * The table manipulator.
     *
     * @var TableManipulator
     */
    private TableManipulator $tableManipulator;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param TableManipulator         $tableManipulator  The table manipulator.
     * @param IFactory                 $factory           The MetaModel factory.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        TableManipulator $tableManipulator,
        IFactory $factory
    ) {
        parent::__construct($scopeDeterminator);
        $this->tableManipulator = $tableManipulator;
        $this->factory          = $factory;
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
    public function handle(EncodePropertyValueFromWidgetEvent $event): void
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'tableName')) {
            return;
        }

        // See #49 (We can no longer find the correct issue number... :().
        $tableName = \strtolower($event->getValue());

        $translator = $event->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        if ('' === $tableName) {
            throw new \RuntimeException($translator->translate('ERR.tableNameNotGiven', 'tl_metamodel'));
        }

        // Force mm_ prefix.
        if (!\str_starts_with($tableName, 'mm_')) {
            $tableName = 'mm_' . $tableName;
        }

        // New model, ensure the table does not exist.
        if (!$event->getModel()->getId()) {
            $this->checkTableName($tableName, $translator);
        } else {
            $dataProvider = $event->getEnvironment()->getDataProvider('tl_metamodel');
            assert($dataProvider instanceof DataProviderInterface);

            // Edited model, ensure the value is unique and then that the table does not exist.
            $oldVersion = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($event->getModel()->getId()));
            assert($oldVersion instanceof ModelInterface);
            if ($oldVersion->getProperty('tableName') !== $event->getModel()->getProperty('tableName')) {
                $this->checkTableName($tableName, $translator);
            }
        }

        $event->setValue($tableName);
    }

    private function checkTableName(string $tableName, TranslatorInterface $translator): void
    {
        try {
            $this->tableManipulator->checkTablename($tableName);
        } catch (InvalidTableNameException $exception) {
            throw new \RuntimeException(
                $translator->translate('ERR.invalidTableName', 'tl_metamodel', ['%table_name%' => $tableName]),
                $exception->getCode(),
                $exception
            );
        }
        $model = $this->factory->getMetaModel($tableName);
        if (null !== $model) {
            throw new \RuntimeException(
                $translator->translate('ERR.tableExists', 'tl_metamodel', ['%table_name%' => $tableName])
            );
        }
    }
}
