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

namespace MetaModels\Filter\Setting;

use Psr\Container\ContainerInterface;

/**
 * Attribute type factory for custom SQL filter settings.
 */
class CustomSqlFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * The Contao framework.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     *
     * @param ContainerInterface $container The service container.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;

        $this
            ->setTypeName('customsql')
            ->setTypeIcon('bundles/metamodelscore/images/icons/filter_customsql.png')
            ->setTypeClass(CustomSql::class)
            ->allowAttributeTypes();
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $filterSettings)
    {
        return new CustomSql(
            $filterSettings,
            $information,
            $this->container
        );
    }
}
