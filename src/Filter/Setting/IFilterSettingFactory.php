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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\IServiceContainerAware;

/**
 * This is the filter settings factory interface.
 *
 * @see IFilterSettingFactory::createCollection() to create a Filter setting collection instance.
 */
interface IFilterSettingFactory extends IServiceContainerAware
{
    /**
     * Register a type factory.
     *
     * @param IFilterSettingTypeFactory $factory The type factory.
     *
     * @return IFilterSettingFactory
     */
    public function addTypeFactory($factory);

    /**
     * Retrieve the filter setting type factory.
     *
     * @param string $type The type name.
     *
     * @return IFilterSettingTypeFactory|null
     */
    public function getTypeFactory($type);

    /**
     * Create a ICollection instance from the id.
     *
     * @param string $settingId The id of the ICollection.
     *
     * @return ICollection The instance of the filter settings or null if not found.
     */
    public function createCollection($settingId);

    /**
     * Retrieve the list of registered filter setting type factories.
     *
     * @return string[]
     */
    public function getTypeNames();
}
