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

use Contao\CoreBundle\DependencyInjection\Attribute\AsOperationForTemplateStudioElement;

/**
 * Delete a variant of the Twig templates of the "attribute" group in the Template Studio.
 *
 * @internal
 */
#[AsOperationForTemplateStudioElement(name: 'delete', priority: -1)]
final class DeleteAttributeVariantOperation extends AbstractDeleteVariantOperation
{
    protected const DATABASE_REFERENCES = ['tl_metamodel_rendersetting' => 'template'];

    #[\Override]
    protected function getPrefix(): string
    {
        return 'metamodels/attribute';
    }
}
