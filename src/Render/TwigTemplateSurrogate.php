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

namespace MetaModels\Render;

use Contao\CoreBundle\Twig\ContaoTwigUtil;
use Contao\CoreBundle\Twig\Interop\ContextFactory;
use Contao\CoreBundle\Twig\Loader\ContaoFilesystemLoader;
use Twig\Environment;

/**
 * Renders a matching Twig template as a surrogate for a legacy MetaModels PHP template.
 *
 * A Twig template "@Contao/metamodels/&lt;group&gt;/&lt;leaf&gt;.html.twig" takes precedence over the legacy ".html5"
 * template (frontend and backend), mirroring Contao's own Twig surrogate. Only the visual "html5" output format is a
 * candidate; the plain text format (search index and sorting) always stays on the PHP engine.
 */
final readonly class TwigTemplateSurrogate
{
    public function __construct(
        private Environment $twig,
        private ContaoFilesystemLoader $loader,
        private ContextFactory $contextFactory,
    ) {
    }

    /**
     * Render the Twig surrogate for the given template or return null to fall back to the legacy PHP template.
     *
     * @param string               $templateName The legacy template name (e.g. "mm_attr_text").
     * @param string               $group        The Twig group ("attribute", "filter" or "item").
     * @param string               $format       The current output format.
     * @param array<string, mixed> $data         The template data.
     */
    public function render(string $templateName, string $group, string $format, array $data): ?string
    {
        // Only the visual output is a candidate for Twig; the plain text format stays on the PHP engine.
        if ('html5' !== $format) {
            return null;
        }

        if (null === ($identifier = $this->getIdentifier($templateName, $group))) {
            return null;
        }

        $candidate = '@Contao/' . $identifier . '.html.twig';
        if (!$this->loader->exists($candidate)) {
            return null;
        }

        // Respect a higher priority legacy ".html5" override in the managed hierarchy (mirror Contao). The actual
        // render below stays theme aware through the loader's internal state; only this precedence check ignores the
        // rarely used theme layer to avoid depending on Contao internal API.
        if ('html5' === ContaoTwigUtil::getExtension($this->loader->getFirst($identifier))) {
            return null;
        }

        return $this->twig->render($candidate, $this->contextFactory->fromData($data));
    }

    /**
     * Build the Twig template identifier ("metamodels/&lt;group&gt;/&lt;leaf&gt;") for a legacy template name.
     *
     * The group comes from the render context (attribute, filter or item); the leaf is the configured template name
     * with the conventional legacy prefix removed - e.g. "mm_attr_text" in the "attribute" group becomes
     * "metamodels/attribute/text".
     *
     * @param string $templateName The legacy template name.
     * @param string $group        The Twig group.
     *
     * @return string|null The identifier or null when no Twig group is set.
     */
    private function getIdentifier(string $templateName, string $group): ?string
    {
        if ('' === $group || '' === $templateName) {
            return null;
        }

        $prefixes = match ($group) {
            'attribute' => ['mm_attr_'],
            'filter'    => ['mm_filteritem_', 'mm_filter_', 'mm_clearall_'],
            'item'      => ['metamodel_', 'mm_'],
            default     => [],
        };

        $leaf = $templateName;
        foreach ($prefixes as $prefix) {
            if (\str_starts_with($leaf, $prefix)) {
                $leaf = \substr($leaf, \strlen($prefix));
                break;
            }
        }

        return 'metamodels/' . $group . '/' . $leaf;
    }
}
