<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Twig\Studio;

use Contao\CoreBundle\Twig\Studio\Operation\AbstractCreateVariantOperation as CoreAbstractCreateVariantOperation;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

use function preg_match;
use function preg_quote;

/**
 * Creates a variant of a MetaModels Twig template ("metamodels/attribute/file" => "metamodels/attribute/file/<name>").
 *
 * Contao only knows its own prefixes. The template of a MetaModels type has an identifier of its own below the group
 * prefix, so the base template is "<prefix>/<leaf>" and a variant has one more segment.
 *
 * @internal
 */
#[AutoconfigureTag('container.service_subscriber', ['id' => 'contao.twig.studio.cache_invalidator'])]
abstract class AbstractCreateVariantOperation extends CoreAbstractCreateVariantOperation
{
    #[\Override]
    public function canExecute(OperationContext $context): bool
    {
        if ($context->isThemeContext()) {
            return false;
        }

        // The ".text" templates carry a dot and have no variants of their own.
        return 1 === preg_match('%^' . preg_quote($this->getPrefix(), '%') . '/[^/.]+$%', $context->getIdentifier());
    }
}
