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

namespace MetaModels\Information;

/**
 * This is a generic attribute information.
 */
class AttributeInformation implements AttributeInformationInterface
{
    use ConfigurationTrait;

    /**
     * The name of the attribute.
     *
     * @var string
     */
    private $name;

    /**
     * The type of the attribute.
     *
     * @var string
     */
    private $type;

    /**
     * Create a new instance.
     *
     * @param string $name          The name of the attribute.
     * @param string $type          The type name.
     * @param array  $configuration The initial configuration.
     */
    public function __construct(string $name, string $type, array $configuration = [])
    {
        $this->name          = $name;
        $this->type          = $type;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }
}
