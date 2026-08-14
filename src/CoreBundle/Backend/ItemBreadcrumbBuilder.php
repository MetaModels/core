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

namespace MetaModels\CoreBundle\Backend;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use Doctrine\DBAL\Connection;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds the trail from a child table up to the MetaModel it hangs below.
 *
 * Standing in the list of a child table, nothing said where one came from. This walks the chain
 * of input screens over their parent table and collects one element per level, each with the name
 * of the MetaModel, the record it belongs to and a link to its listing.
 *
 * The chain is read through the ViewCombination rather than from the database, because which
 * input screen applies - and with it the caption and the parent table - depends on the groups of
 * the user at hand. Reading it any other way would show a caption here that differs from the one
 * in the mask one is standing in.
 */
final class ItemBreadcrumbBuilder
{
    /**
     * How deep the chain may go before it is treated as circular.
     *
     * A screen pointing at itself, directly or over a few corners, would otherwise spin here
     * forever. The value is far above anything a sane configuration reaches.
     */
    private const int MAX_DEPTH = 20;

    public function __construct(
        private readonly ViewCombination $viewCombination,
        private readonly ItemLabelRenderer $labelRenderer,
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Build the trail for a listing of a child table.
     *
     * @param string      $tableName The table being listed.
     * @param string|null $parentId  The serialised id of the record the listing hangs below.
     * @param string|null $currentId The serialised id of the record being edited, if any.
     *
     * @return list<array{table: string, label: string, url: string|null}>
     *     Ordered from the base of the chain to the current view. Empty where there is no chain,
     *     which is the case for every standalone listing.
     */
    public function build(string $tableName, ?string $parentId, ?string $currentId = null): array
    {
        $parentId  = $this->idFrom($parentId);
        $currentId = $this->idFrom($currentId);

        $screen = $this->screenOf($tableName);
        if (null === $screen || 'ctable' !== ($screen['meta']['rendertype'] ?? '')) {
            return [];
        }

        // Collected from the current view upwards and turned around at the end - walking is only
        // possible in that direction, reading is only sensible in the other.
        $trail = [
            [
                'table' => $tableName,
                'label' => $this->modelLabel($tableName),
                'record' => $currentId,
            ],
        ];

        $table  = (string) ($screen['meta']['ptable'] ?? '');
        $record = $parentId;

        for ($depth = 0; '' !== $table && $depth < self::MAX_DEPTH; $depth++) {
            $trail[] = [
                'table'  => $table,
                'label'  => $this->modelLabel($table),
                'record' => $record,
            ];

            $parentScreen = $this->screenOf($table);
            if (null === $parentScreen || 'ctable' !== ($parentScreen['meta']['rendertype'] ?? '')) {
                break;
            }

            // One level up the record of this level becomes the child, so its own pid names the
            // record of the level above.
            $record = null === $record ? null : $this->parentIdOf($table, $record);
            $table  = (string) ($parentScreen['meta']['ptable'] ?? '');
        }

        $trail = \array_reverse($trail);
        $base  = $trail[0]['table'];

        return \array_map(
            fn(array $level): array => [
                'table' => $level['table'],
                'label' => $this->labelOf($level['table'], $level['label'], $level['record']),
                'url'   => $this->urlOf($base, $level['table'], $level['record']),
            ],
            $trail
        );
    }

    /**
     * Retrieve the input screen of a table, if it has one.
     *
     * @param string $tableName The table.
     *
     * @return array<string, mixed>|null
     */
    private function screenOf(string $tableName): ?array
    {
        /** @var array<string, mixed>|null $screen */
        $screen = $this->viewCombination->getScreen($tableName);

        return $screen;
    }

    /**
     * Retrieve the caption of a MetaModel, falling back to its name.
     *
     * @param string $tableName The table.
     *
     * @return string
     */
    private function modelLabel(string $tableName): string
    {
        $screen = $this->screenOf($tableName);
        if (null === $screen) {
            // No screen at all - the chain ended at a plain Contao table.
            return $tableName;
        }

        /** @var array<string, string> $labels */
        $labels = $screen['label'] ?? [];

        $language = $this->requestStack->getCurrentRequest()?->getLocale() ?? '';

        return $labels[$language] ?? $labels[''] ?? $tableName;
    }

    /**
     * Compose the label of one level: the MetaModel, and the record where one is named.
     *
     * @param string      $tableName The table.
     * @param string      $model     The caption of the MetaModel.
     * @param string|null $recordId  The record of this level.
     *
     * @return string
     */
    private function labelOf(string $tableName, string $model, ?string $recordId): string
    {
        if (null === $recordId || '' === $recordId) {
            return $model;
        }

        $screen  = $this->screenOf($tableName);
        $pattern = (string) ($screen['meta']['subheadline'] ?? '');
        $record  = $this->recordOf($tableName, $recordId);

        if (null === $record) {
            return $model;
        }

        $label = $this->labelRenderer->render($pattern, $record);

        // Without a pattern the record cannot be named - the id at least says which one it is,
        // where the bare name of the MetaModel would look like no record was involved at all.
        return $model . ': ' . ('' !== $label ? $label : $recordId);
    }

    /**
     * Fetch a record.
     *
     * @param string $tableName The table.
     * @param string $recordId  The id.
     *
     * @return array<string, mixed>|null
     */
    private function recordOf(string $tableName, string $recordId): ?array
    {
        $record = $this->connection
            ->createQueryBuilder()
            ->select('*')
            ->from($tableName, 't')
            ->where('t.id=:id')
            ->setParameter('id', $recordId)
            ->executeQuery()
            ->fetchAssociative();

        return false === $record ? null : $record;
    }

    /**
     * Fetch the parent id of a record.
     *
     * @param string $tableName The table.
     * @param string $recordId  The id.
     *
     * @return string|null
     */
    private function parentIdOf(string $tableName, string $recordId): ?string
    {
        $parentId = $this->connection
            ->createQueryBuilder()
            ->select('t.pid')
            ->from($tableName, 't')
            ->where('t.id=:id')
            ->setParameter('id', $recordId)
            ->executeQuery()
            ->fetchOne();

        return false === $parentId || null === $parentId ? null : (string) $parentId;
    }

    /**
     * Build the link to the listing of one level.
     *
     * The route names the table of the backend module - the base of the chain - while the level
     * itself travels as a query parameter, which is how MetaModels addresses a child listing.
     *
     * @param string      $base      The table at the base of the chain.
     * @param string      $tableName The table of this level.
     * @param string|null $recordId  The record of this level.
     *
     * @return string|null
     */
    private function urlOf(string $base, string $tableName, ?string $recordId): ?string
    {
        // The chain may end at a plain Contao table, which this route knows nothing about.
        if (null === $this->screenOf($base)) {
            return null;
        }

        $parameters = ['tableName' => $base];

        if ($base !== $tableName) {
            $parameters['table'] = $tableName;
            $parameters['pid']   = $this->parentOfLevel($tableName, $recordId);

            if (null === $parameters['pid']) {
                return null;
            }
        }

        return $this->urlGenerator->generate('metamodels.metamodel', $parameters);
    }

    /**
     * Build the serialised parent id a child listing needs.
     *
     * @param string      $tableName The table of this level.
     * @param string|null $recordId  The record of this level.
     *
     * @return string|null
     */
    private function parentOfLevel(string $tableName, ?string $recordId): ?string
    {
        $screen = $this->screenOf($tableName);
        $parent = (string) ($screen['meta']['ptable'] ?? '');

        if ('' === $parent || null === $recordId) {
            return null;
        }

        $parentId = $this->parentIdOf($tableName, $recordId);

        return null === $parentId ? null : $parent . '::' . $parentId;
    }

    /**
     * Pull the plain id out of a serialised model id.
     *
     * Both callers - the controller and the menu listener - hand over what stood in the query, so
     * the unpacking belongs here rather than in each of them.
     *
     * @param string|null $parameter The parameter as it came in.
     *
     * @return string|null
     */
    private function idFrom(?string $parameter): ?string
    {
        if (null === $parameter || '' === $parameter) {
            return null;
        }

        try {
            return (string) ModelId::fromSerialized($parameter)->getId();
        } catch (\Exception) {
            // Not a model id - better no element than a wrong one.
            return null;
        }
    }
}
