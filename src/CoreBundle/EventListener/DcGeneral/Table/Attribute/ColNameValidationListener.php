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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Exceptions\Database\InvalidColumnNameException;
use MetaModels\Helper\TableManipulator;
use MetaModels\IFactory;

/**
 * This class takes care of validating the column name of an attribute.
 */
class ColNameValidationListener extends BaseListener
{
    /**
     * The table manipulator.
     *
     * @var TableManipulator
     */
    private TableManipulator $tableManipulator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param TableManipulator         $tableManipulator  The table manipulator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IAttributeFactory $attributeFactory,
        IFactory $factory,
        TableManipulator $tableManipulator
    ) {
        parent::__construct($scopeDeterminator, $attributeFactory, $factory);
        $this->tableManipulator = $tableManipulator;
    }

    /**
     * Validate the column name and ensure that the column does not exist already.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When the column name is illegal or duplicate.
     */
    public function handle(EncodePropertyValueFromWidgetEvent $event): void
    {
        if (!parent::wantToHandle($event) || ($event->getProperty() !== 'colname')) {
            return;
        }

        $oldColumnName = $event->getModel()->getProperty($event->getProperty());
        $columnName    = $event->getValue();
        $metaModel     = $this->getMetaModelByModelPid($event->getModel());
        $translator    = $event->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        if ((!$columnName) || $oldColumnName !== $columnName) {
            try {
                $this->tableManipulator->checkColumnName($columnName);
            } catch (InvalidColumnNameException $exception) {
                throw new \RuntimeException(
                    $translator->translate(
                        'ERR.' . ($this->tableManipulator->isSystemColumn($columnName)
                            ? 'systemColumn'
                            : 'invalidColumnName'),
                        'tl_metamodel_attribute',
                        [
                            '%col_name%'   => $columnName,
                            '%table_name%' => $metaModel->getTableName()
                        ]
                    ),
                    0,
                    $exception
                );
            }

            $colNames = \array_keys($metaModel->getAttributes());
            if (\in_array($columnName, $colNames)) {
                throw new \RuntimeException(
                    $translator->translate(
                        'ERR.columnExists',
                        'tl_metamodel_attribute',
                        [
                            '%col_name%'   => $columnName,
                            '%table_name%' => $metaModel->getTableName()
                        ]
                    )
                );
            }
        }
    }
}
