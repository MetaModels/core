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

use Contao\InsertTags;
use DependencyInjection\Container\LegacyDependencyInjectionContainer;
use Doctrine\DBAL\Connection;

/**
 * Attribute type factory for custom SQL filter settings.
 */
class CustomSqlFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $database;

    /**
     * The event dispatcher.
     *
     * @var InsertTags
     */
    private $insertTags;

    /**
     * The legacy MetaModels service container retriever callback.
     *
     * @var \Closure
     *
     * @deprecated Only here as gateway to the deprecated service container.
     */
    private $legacyDic;

    /**
     * {@inheritDoc}
     *
     * @param Connection $database   The database.
     * @param InsertTags $insertTags The insert tag handler.
     */
    public function __construct(
        Connection $database,
        InsertTags $insertTags
    ) {
        parent::__construct();

        $this->database   = $database;
        $this->insertTags = $insertTags;

        $this
            ->setTypeName('customsql')
            ->setTypeIcon('bundles/metamodelscore/images/icons/filter_customsql.png')
            ->setTypeClass(CustomSql::class)
            ->allowAttributeTypes();
    }

    /**
     * Set the legacy DIC retriever function.
     *
     * @return void
     */
    public function setLegacyDic(\Closure $callback)
    {
        $this->legacyDic = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $filterSettings)
    {
        return new CustomSql(
            $filterSettings,
            $information,
            $this->database,
            $this->insertTags,
            function () {
                static $container;
                if (!$container) {
                    $container = $this->legacyDic->__invoke();
                }

                return $container;
            }
        );
    }
}
