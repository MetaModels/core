<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_unique;
use function array_values;
use function is_array;

/**
 * Registers the MetaModels storage tables that hold file references with the
 * "ignore tables" list of inspiredminds/contao-file-usage.
 *
 * These tables are already covered by the dedicated MetaModels file usage
 * providers, so the generic database provider of the file usage extension must
 * not scan them again - otherwise every reference would be reported twice.
 */
class RegisterFileUsageIgnoreTablesPass implements CompilerPassInterface
{
    /**
     * The parameter holding the tables to be ignored by the file usage extension.
     */
    private const PARAMETER = 'contao_file_usage.ignore_tables';

    /**
     * MetaModels attribute storage tables that store file references as text
     * (plain UUID or insert tag) and provide their own file usage provider.
     */
    private const IGNORE_TABLES = [
        'tl_metamodel_tablemulti',
        'tl_metamodel_translatedtablemulti',
    ];

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::PARAMETER)) {
            return;
        }

        $tables = $container->getParameter(self::PARAMETER);
        if (!is_array($tables)) {
            return;
        }

        $container->setParameter(
            self::PARAMETER,
            array_values(array_unique([...$tables, ...self::IGNORE_TABLES]))
        );
    }
}
