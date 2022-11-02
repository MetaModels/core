<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use Doctrine\DBAL\Driver\Connection;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsServiceContainer;

/**
 * Condition for the default palette.
 */
class InputScreenRenderModeIs implements PropertyConditionInterface
{
    /**
     * The expected property value.
     *
     * @var string
     */
    protected $desiredState;

    /**
     * The expected property value.
     *
     * @var string[]
     */
    protected static $stateBuffer;

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param string          $desiredState The desired state.
     *
     * @param Connection|null $connection   Database connection.
     */
    public function __construct($desiredState, Connection $connection = null)
    {
        $this->setRenderMode($desiredState);

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
        }

        $this->connection = $connection;
    }

    /**
     * Set the desired state.
     *
     * @param string $desiredState The desired state.
     *
     * @return InputScreenRenderModeIs
     */
    public function setRenderMode($desiredState)
    {
        $this->desiredState = $desiredState;

        return $this;
    }

    /**
     * Retrieve the desired state.
     *
     * @return string
     */
    public function getRenderMode()
    {
        return $this->desiredState;
    }

    /**
     * Retrieve the type name from an attribute.
     *
     * @param int $value The id of an input screen.
     *
     * @return string
     */
    public function getInputScreenRenderMode($value)
    {
        if (!isset(self::$stateBuffer[$value])) {
            $statement =$this->connection
                ->createQueryBuilder()
                ->select('t.rendermode')
                ->from('tl_metamodel_dca', 't')
                ->where('t.id=:id')
                ->setParameter('id', $value)
                ->setMaxResults(1)
                ->execute();

            self::$stateBuffer[$value] = $statement->fetch(\PDO::FETCH_OBJ)->rendermode;
        }

        return self::$stateBuffer[$value];
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if ($input && $input->hasPropertyValue('pid')) {
            $value = $input->getPropertyValue('pid');
        } elseif ($model) {
            $value = $model->getProperty('pid');
        } else {
            return false;
        }

        return $this->getInputScreenRenderMode($value) == $this->getRenderMode();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated
     */
    protected function getServiceContainer(): IMetaModelsServiceContainer
    {
        return System::getContainer()->get(MetaModelsServiceContainer::class);
    }
}
