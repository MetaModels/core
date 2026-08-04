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

namespace MetaModels\CoreBundle\DcGeneral;

use Contao\BackendUser;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Builds the "jump to the relation table" button that relation attributes add next to their label.
 *
 * The button is never simply omitted: a user who sees it at one field and not at the next has no way of telling why.
 * When the target cannot be opened it is rendered greyed out with a tooltip that names the reason - see
 * .claude/relations-stift.md for the reasoning behind that.
 *
 * Lives in the core rather than in the attribute packages because every relation attribute needs the same thing;
 * the packages only have to know their own target table.
 */
final class RelationJumpBuilder
{
    /**
     * Reason why no link is rendered - decides tooltip and cursor.
     */
    private const REASON_LOCKED    = 'locked';
    private const REASON_FORBIDDEN = 'forbidden';
    private const REASON_NO_MODULE = 'no_module';

    /**
     * Not a reason but the decision to render nothing at all - used where a button would be pointless rather than
     * unavailable, e.g. for a MetaModel that is only ever maintained inside its parent.
     */
    private const REASON_SKIP = 'skip';

    /**
     * @param array<string, string> $contaoModules Maps a Contao table to the backend module that edits it.
     */
    public function __construct(
        private readonly ViewCombination $viewCombination,
        private readonly IconBuilder $iconBuilder,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly array $contaoModules,
    ) {
    }

    /**
     * Render the button for the given target table.
     *
     * @param string $targetTable The table the relation points at.
     * @param bool   $locked      Whether the field itself is readonly or disabled.
     *
     * @return string The markup to append to the widget's xlabel, empty when there is nothing to show.
     */
    public function build(string $targetTable, bool $locked): string
    {
        if ('' === $targetTable) {
            return '';
        }

        [$url, $reason] = $this->resolveTarget($targetTable);

        if (self::REASON_SKIP === $reason) {
            return '';
        }

        if (null !== $url && $locked) {
            $reason = self::REASON_LOCKED;
            $url    = null;
        }

        return null === $url ? $this->renderDisabled($targetTable, (string) $reason) : $this->renderLink($url);
    }

    /**
     * Determine the target URL, or the reason why there is none.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveTarget(string $targetTable): array
    {
        if (\str_starts_with($targetTable, 'mm_')) {
            if (null === $this->viewCombination->getCombination($targetTable)) {
                return [null, self::REASON_FORBIDDEN];
            }

            // Only stand-alone input screens have a backend module of their own. A MetaModel that is maintained as a
            // child is reached through the operation in its parent list instead - not a case for this button.
            if (!\array_key_exists($targetTable, $this->viewCombination->getStandalone())) {
                return [null, self::REASON_SKIP];
            }

            return [$this->router->generate('metamodels.metamodel', ['tableName' => $targetTable]), null];
        }

        // Contao has no generic backend module per table, so the mapping is configured.
        // Unlike the child table above the target does exist here, it just has no module configured - that is worth
        // showing greyed out, because an administrator can fix it by extending the mapping.
        if (null === ($module = $this->contaoModules[$targetTable] ?? null)) {
            return [null, self::REASON_NO_MODULE];
        }

        if (!$this->hasModuleAccess($module)) {
            return [null, self::REASON_FORBIDDEN];
        }

        return [$this->router->generate('contao_backend', ['do' => $module]), null];
    }

    private function hasModuleAccess(string $module): bool
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!($user instanceof BackendUser)) {
            return false;
        }

        return (bool) $user->isAdmin || (bool) $user->hasAccess($module, 'modules');
    }

    private function renderLink(string $url): string
    {
        return \sprintf(
            '<a href="%s" title="%s" target="_blank" style="padding-left:3px">%s</a>',
            $url,
            $this->escape($this->trans('relation_jump.title')),
            $this->icon()
        );
    }

    private function renderDisabled(string $targetTable, string $reason): string
    {
        // Deliberately not an anchor: without a href it would neither be focusable nor do anything, and the URL of a
        // target the user must not open has no business being in the markup.
        return \sprintf(
            '<span title="%s" aria-disabled="true" style="padding-left:3px;cursor:%s;filter:saturate(0);opacity:.5">'
            . '%s</span>',
            $this->escape($this->trans('relation_jump.title_' . $reason, ['%table%' => $targetTable])),
            self::REASON_LOCKED === $reason ? 'default' : 'not-allowed',
            $this->icon()
        );
    }

    private function icon(): string
    {
        // No forced height: the button sits inside the field header, whose line box is 14px high. Blowing the icon
        // up to the 24px the content element wizard uses makes it hang over the edge, crowd the field below and cut
        // off the descenders of the label. The icon's own size fits the line.
        return $this->iconBuilder->getBackendIconImageTag(
            'system/themes/flexible/icons/alias.svg',
            $this->trans('relation_jump.label'),
            'style="vertical-align:text-bottom"'
        );
    }

    /** @param array<string, string> $parameters */
    private function trans(string $key, array $parameters = []): string
    {
        return $this->translator->trans($key, $parameters, 'metamodels_default');
    }

    private function escape(string $value): string
    {
        return \htmlspecialchars($value, ENT_QUOTES);
    }
}
