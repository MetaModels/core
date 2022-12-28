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

use Doctrine\DBAL\Exception\NonUniqueFieldNameException;

/**
 * This is the legacy schema manager used for bc reasons.
 *
 * @deprecated Since 2.1 - to be removed in 3.0
 */
class LegacySchemaManager implements SchemaManagerInterface
{
    /**
     * {@inheritDoc}
     */
    public function preprocess(SchemaInformation $information): void
    {
        // No-op.
    }

    /**
     * {@inheritDoc}
     */
    public function process(SchemaInformation $information): void
    {
        if (!$information->has(LegacySchemaInformation::class)) {
            return;
        }
        /** @var LegacySchemaInformation $legacySchema */
        $legacySchema = $information->get(LegacySchemaInformation::class);

        foreach ($legacySchema->getAttributes() as $attribute) {
            try {
                $attribute->initializeAUX();
            } catch (\Throwable $exception) {
                // Transcribe known exceptions.
                switch (true) {
                    case $exception instanceof NonUniqueFieldNameException:
                        if (preg_match(
                            '/SQLSTATE\[42S21\]: Column already exists: 1060 Duplicate column name \'(?P<name>.+)\'/',
                            $exception->getMessage(),
                            $matches
                        )) {
                            // @codingStandardsIgnoreStart
                            @trigger_error('Column already exists: "' . $matches['name'] . '"');
                            // @codingStandardsIgnoreEnd
                            continue 2;
                        }
                    default:
                }

                // @codingStandardsIgnoreStart
                @trigger_error(
                    sprintf(
                        'Ignored exception "%1$s" for attribute "%2$s": %3$s',
                        get_class($exception),
                        $attribute->getColName(),
                        $exception->getMessage()
                    ),
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function postprocess(SchemaInformation $information): void
    {
        // No-op.
    }

    /**
     * {@inheritDoc}
     */
    public function validate(SchemaInformation $information): array
    {
        if (!$information->has(LegacySchemaInformation::class)) {
            return [];
        }
        /** @var LegacySchemaInformation $legacySchema */
        $legacySchema = $information->get(LegacySchemaInformation::class);

        $tasks = [];
        foreach ($legacySchema->getAttributes() as $attribute) {
            $tasks[] = sprintf(
                '(Re-)Initialize attribute "%1$s" (type: "%2$s") via legacy method.',
                $attribute->getColName(),
                $attribute->get('type')
            );
        }

        return $tasks;
    }
}
