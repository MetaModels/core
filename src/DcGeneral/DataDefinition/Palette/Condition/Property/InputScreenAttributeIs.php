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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Condition for the default palette.
 *
 * @deprecated Use AttributeByIdIsOfType instead.
 */
class InputScreenAttributeIs extends AttributeByIdIsOfType
{
    /**
     * Create a new instance.
     *
     * @param string          $attributeType The attribute type name.
     * @param Connection|null $connection    Database connection.
     */
    public function __construct($attributeType, Connection $connection = null)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' . __CLASS__ . '" is deprecated and will get removed in MetaModels 3.0. ' .
            'Use "' . AttributeByIdIsOfType::class . '" instead',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
        }

        parent::__construct($attributeType, $connection, 'attr_id');
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated 
     */
    protected function getServiceContainer()
    {
        return System::getContainer()->get('metamodels.service_container');
    }
}
