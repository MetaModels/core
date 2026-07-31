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
 * template (frontend and backend), mirroring Contao's own Twig surrogate.
 *
 * The plain text format (search index, sorting, group headers) works the same way, but its templates carry an
 * additional ".text" in the name: "&lt;leaf&gt;.text.html.twig" surrogates the legacy "&lt;leaf&gt;.text".
 *
 * The doubled extension is not cosmetic. Contao derives the template identifier by stripping the trailing
 * ".html.twig" resp. ".twig", and it refuses to mix types under one identifier: a "&lt;leaf&gt;.text.twig" beside a
 * "&lt;leaf&gt;.html.twig" would share the identifier "&lt;leaf&gt;" but count as a different type, which makes
 * ContaoFilesystemLoader abort the whole hierarchy with an OutOfBoundsException - the entire back end and front end
 * would answer with HTTP 500. Keeping ".html.twig" as the real extension puts the text variant under its own
 * identifier "&lt;leaf&gt;.text". The same naming is already in use for the notelist mail template.
 */
final readonly class TwigTemplateSurrogate
{
    /**
     * The output formats that can be surrogated, mapped to the suffix of the Twig template name.
     */
    private const SUFFIX_BY_FORMAT = [
        'html5' => '.html.twig',
        'text'  => '.text.html.twig',
    ];

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
        if (null === ($suffix = self::SUFFIX_BY_FORMAT[$format] ?? null)) {
            return null;
        }

        if (null === ($identifier = $this->getIdentifier($templateName, $group))) {
            return null;
        }

        // The ".text" of the suffix becomes part of the identifier ("<leaf>.text"), the real extension stays
        // ".html.twig" - see the class comment on why.
        $candidate = '@Contao/' . $identifier . $suffix;
        if (!$this->loader->exists($candidate)) {
            return null;
        }

        // Respect a higher priority legacy ".html5" override in the managed hierarchy (mirror Contao). The actual
        // render below stays theme aware through the loader's internal state; only this precedence check ignores the
        // rarely used theme layer to avoid depending on Contao internal API. The text identifier has no legacy
        // counterpart in the hierarchy, so the check only applies to the visual format.
        if ('html5' === $format && 'html5' === ContaoTwigUtil::getExtension($this->loader->getFirst($identifier))) {
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
