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

use Contao\CoreBundle\Twig\Studio\Operation\AbstractDeleteVariantOperation as CoreAbstractDeleteVariantOperation;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;
use Doctrine\DBAL\Connection;
use MetaModels\Render\TwigTemplateNames;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function explode;
use function preg_match;
use function preg_quote;
use function preg_replace;

/**
 * Deletes a variant of a MetaModels Twig template.
 *
 * Settings that used the variant fall back to the base template. MetaModels stores the legacy template name
 * ("mm_attr_file/variant"), not the Twig identifier, so the references are migrated here.
 *
 * @internal
 */
#[AutoconfigureTag('container.service_subscriber', ['id' => 'contao.twig.studio.cache_invalidator'])]
abstract class AbstractDeleteVariantOperation extends CoreAbstractDeleteVariantOperation
{
    /**
     * The "<table>.<field>" columns that store the legacy template name.
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
    public function execute(Request $request, OperationContext $context): Response
    {
        $response = parent::execute($request, $context);

        if (!$this->getUserTemplatesStorage()->fileExists($context->getUserTemplatesStoragePath())) {
            $this->fallBackToBaseTemplate($context->getIdentifier());
        }

        return $response;
    }

    private function fallBackToBaseTemplate(string $identifier): void
    {
        $from = TwigTemplateNames::legacyName($identifier);
        $to   = TwigTemplateNames::legacyName((string) preg_replace('%/[^/]+$%', '', $identifier));
        if (null === $from || null === $to) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get('database_connection');
        foreach (static::DATABASE_REFERENCES as $reference) {
            [$table, $field] = explode('.', $reference, 2);

            $connection->update($table, [$field => $to], [$field => $from]);
        }
    }
}
