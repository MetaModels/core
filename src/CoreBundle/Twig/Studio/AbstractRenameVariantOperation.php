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

use Contao\CoreBundle\Twig\Studio\Operation\AbstractRenameVariantOperation as CoreAbstractRenameVariantOperation;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;
use Doctrine\DBAL\Connection;
use MetaModels\Render\TwigTemplateNames;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function preg_match;
use function preg_quote;
use function preg_replace;

/**
 * Renames a variant of a MetaModels Twig template.
 *
 * MetaModels stores the legacy template name ("mm_attr_file/variant"), not the Twig identifier, so the references
 * are migrated here instead of by the Contao base class.
 *
 * @internal
 */
#[AutoconfigureTag('container.service_subscriber', ['id' => 'contao.twig.studio.cache_invalidator'])]
abstract class AbstractRenameVariantOperation extends CoreAbstractRenameVariantOperation
{
    /**
     * The columns ("table" => "field") that store the legacy template name.
     */
    protected const DATABASE_REFERENCES = [];

    #[\Override]
    public function canExecute(OperationContext $context): bool
    {
        if ($context->isThemeContext() || !$this->userTemplateExists($context)) {
            return false;
        }

        return 1 === preg_match(
            '%^' . preg_quote($this->getPrefix(), '%') . '/[^/.]+/[^/]+$%',
            $context->getIdentifier()
        );
    }

    #[\Override]
    public function execute(Request $request, OperationContext $context): Response|null
    {
        $response = parent::execute($request, $context);

        // Only when the file has really been moved, the base class refuses to overwrite an existing variant.
        $fragment = $request->request->getString('identifier_fragment');
        if ('' !== $fragment && !$this->getUserTemplatesStorage()->fileExists($context->getUserTemplatesStoragePath())) {
            $this->migrateLegacyNames(
                $context->getIdentifier(),
                (string) preg_replace('%/[^/]+$%', '/' . $fragment, $context->getIdentifier())
            );
        }

        return $response;
    }

    private function migrateLegacyNames(string $fromIdentifier, string $toIdentifier): void
    {
        $source = TwigTemplateNames::legacyName($fromIdentifier);
        $target = TwigTemplateNames::legacyName($toIdentifier);
        if (null === $source || null === $target) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get('database_connection');
        foreach (static::DATABASE_REFERENCES as $table => $field) {
            $connection->update($table, [$field => $target], [$field => $source]);
        }
    }
}
