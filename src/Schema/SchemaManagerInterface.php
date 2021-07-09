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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\Schema;

/**
 * This interface describes a schema manager.
 */
interface SchemaManagerInterface
{
    /**
     * Pre-process the schema information.
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return void
     */
    public function preprocess(SchemaInformation $information): void;

    /**
     * Pre-process the schema information.
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return void
     */
    public function process(SchemaInformation $information): void;

    /**
     * Pre-process the schema information.
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return void
     */
    public function postprocess(SchemaInformation $information): void;

    /**
     * List the tasks that will be performed for the passed information
     *
     * @param SchemaInformation $information The schema information.
     *
     * @return string[]
     */
    public function validate(SchemaInformation $information): array;
}
