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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\InformationProvider;

use MetaModels\Information\MetaModelInformation;

/**
 * This collects information for MetaModels.
 */
interface InformationProviderInterface
{
    /**
     * Gets the names of known MetaModels.
     *
     * @return list<string>
     */
    public function getNames(): array;

    /**
     * Obtain the schema for a single MetaModel.
     *
     * If the provider does not know the MetaModel, it must ignore it.
     *
     * @param MetaModelInformation $information The information to which to add.
     *
     * @return void
     */
    public function getInformationFor(MetaModelInformation $information): void;
}
