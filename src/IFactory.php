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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

/**
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, create an instance of the factory.
 * and call {@link \MetaModels\IFactory::getMetaModel()}.
 *
 * If you only have the id of a MetaModel, you can translate it to the MetaModel-name by invoking
 * {@link \MetaModels\IFactory::translateIdToMetaModelName()} and then perform just as normal.
 */
interface IFactory extends IServiceContainerAware
{
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getServiceContainer();

    /**
     * Translate a MetaModel id to the corresponding name of the MetaModel.
     *
     * @param string $metaModelId The id of the MetaModel.
     *
     * @return string The name of the MetaModel.
     */
    public function translateIdToMetaModelName($metaModelId);

    /**
     * Create a MetaModel instance.
     *
     * @param string $metaModelName The name of the MetaModel to create.
     *
     * @return IMetaModel|null
     */
    public function getMetaModel($metaModelName);

    /**
     * Query for all known MetaModel names.
     *
     * @return string[] all MetaModel names as string array.
     */
    public function collectNames();
}
