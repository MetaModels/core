<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\InsertTag;

/**
 * This represents a string literal in an insert tag node list.
 */
final class LiteralNode implements NodeInterface
{
    /**
     * The node value.
     *
     * @var string
     */
    private string $value;

    /**
     * Create a new instance.
     *
     * @param string $value The node value.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Obtrain the string value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function asString(): string
    {
        return $this->getValue();
    }
}
