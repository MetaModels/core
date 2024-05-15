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

declare(strict_types=1);

namespace MetaModels\Schema;

use MetaModels\Attribute\IAttribute;

/**
 * This schema information is used for bc to MetaModels 2.0 interfaces.
 *
 * @deprecated Since 2.1 - to be removed in 3.0
 */
class LegacySchemaInformation implements SchemaInformationInterface
{
    /**
     * The attributes.
     *
     * @var IAttribute[]
     */
    private $attributes = [];

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Add an attribute
     *
     * @param IAttribute $attribute The attribute to add.
     *
     * @return void
     */
    public function addAttribute(IAttribute $attribute): void
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Retrieve attributes.
     *
     * @return IAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
