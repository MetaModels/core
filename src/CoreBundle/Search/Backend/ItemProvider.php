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

namespace MetaModels\CoreBundle\Search\Backend;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Search\Backend\Document;
use Contao\CoreBundle\Search\Backend\Hit;
use Contao\CoreBundle\Search\Backend\Provider\ProviderInterface;
use Contao\CoreBundle\Search\Backend\ReindexConfig;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\ITranslated;
use MetaModels\CoreBundle\Backend\ItemLabelRenderer;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Makes MetaModels items findable through Contao's back end search (5.5+).
 *
 * Contao's own "contao.backend_search_provider" tag only picks up tables whose "dataContainer"
 * is exactly Contao\DC_Table (see TableDataContainerProvider). Every MetaModels table - the
 * tl_metamodel_* configuration tables and every dynamically created item table alike - uses
 * dc-general's General::class instead, so it is skipped there entirely. This provider covers
 * the item tables specifically (an item's own attribute values, not the tl_metamodel_* setup
 * tables), mirroring TableDataContainerProvider's approach but through MetaModels' own APIs:
 * IFactory for the item data, ViewCombination for both the permission check and the label
 * pattern (tl_metamodel_dca_combine - see the "mm-rechte-ueber-dca-combine" note on why that is
 * the correct place, not Contao's regular user-group table rights), and the "metamodels.metamodel"
 * route (same URL shape the file-usage provider already builds) for the edit link.
 *
 * @experimental Follows the @experimental status of Contao's own backend search provider API.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ItemProvider implements ProviderInterface
{
    public const TYPE_PREFIX = 'metamodels.item.';

    private string $refererId = '';

    public function __construct(
        private readonly IFactory $factory,
        private readonly ViewCombination $viewCombination,
        private readonly ItemLabelRenderer $labelRenderer,
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly string $csrfTokenName,
    ) {
    }

    #[\Override]
    public function supportsType(string $type): bool
    {
        return str_starts_with($type, self::TYPE_PREFIX);
    }

    /**
     * @return iterable<Document>
     */
    #[\Override]
    public function updateIndex(ReindexConfig $config): iterable
    {
        foreach ($this->getTables($config) as $table) {
            try {
                // Instantiating the MetaModel (MetaModels\Factory eagerly builds every attribute
                // of the table), building the attribute list, or fetching the id list can throw
                // for a MetaModel that has nothing to do with search at all - e.g. a "rating"
                // attribute's constructor assumes an HTTP request is available, which does not
                // hold on the CLI/messenger worker. One broken table must not sink the whole
                // reindex job.
                $metaModel = $this->factory->getMetaModel($table);
                if (null === $metaModel) {
                    continue;
                }

                $searchableColNames = $this->getSearchableColNames($metaModel);
                if ([] === $searchableColNames) {
                    continue;
                }

                $type = $this->getTypeFromTable($table);
                $ids  = $config->getLimitedDocumentIds()->getDocumentIdsForType($type);
                if ([] === $ids) {
                    $ids = array_map('strval', $metaModel->getIdsFromFilter($metaModel->getEmptyFilter()));
                }

                $entries = $this->collectSearchableTexts($metaModel, $ids, $searchableColNames);
            } catch (\Throwable) {
                continue;
            }

            foreach ($entries as $docId => $entry) {
                $searchableContent = implode(' ', array_filter(array_unique($entry['values'])));
                if ('' === $searchableContent) {
                    continue;
                }

                $metadata = ['table' => $table, 'itemId' => $entry['itemId']];
                if (null !== $entry['language']) {
                    $metadata['language'] = $entry['language'];
                }

                // $docId came back as a PHP array key, which silently casts purely numeric strings
                // to int - Document's constructor is string-typed under strict_types. Psalm trusts
                // the declared array-shape key type ("string") literally and calls this redundant,
                // but PHP's own key coercion happens regardless of any docblock; the cast is real.
                /** @psalm-suppress RedundantCastGivenDocblockType */
                yield (new Document((string) $docId, $type, $searchableContent))->withMetadata($metadata);
            }
        }
    }

    #[\Override]
    public function convertDocumentToHit(Document $document): Hit|null
    {
        $table     = $this->getTableFromDocument($document);
        $metaModel = $this->factory->getMetaModel($table);
        if (null === $metaModel) {
            return null;
        }

        $itemId   = $document->getMetadata()['itemId'] ?? $document->getId();
        $language = $document->getMetadata()['language'] ?? null;

        $item = $metaModel->findById($itemId);
        if (null === $item) {
            return null;
        }

        $editUrl = $this->buildEditUrl($table, $itemId, $language);

        return (new Hit($document, $this->buildTitle($metaModel, $itemId, $language), $editUrl))
            ->withEditUrl($editUrl)
            ->withContext($document->getSearchableContent())
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function isDocumentGranted(TokenInterface $token, Document $document): bool
    {
        // The token argument is intentionally unused. MetaModels' permission model
        // (tl_metamodel_dca_combine, resolved through ViewCombination) is only wired up for the
        // currently authenticated user via Symfony's token storage, not for an arbitrary token -
        // there is no per-table alexf permission to fall back to, MetaModels deliberately does
        // not register in $GLOBALS['BE_MOD'] (see the "mm-rechte-ueber-dca-combine" note).
        return null !== $this->viewCombination->getScreen($this->getTableFromDocument($document));
    }

    #[\Override]
    public function convertTypeToVisibleType(string $type): string
    {
        $table = substr($type, \strlen(self::TYPE_PREFIX));

        return $this->factory->getMetaModel($table)?->getName() ?? $table;
    }

    /**
     * @return array<int, string>
     */
    private function getTables(ReindexConfig $config): array
    {
        $tables = $this->factory->collectNames();

        if ($config->getLimitedDocumentIds()->isEmpty()) {
            return $tables;
        }

        return array_filter(
            $tables,
            fn (string $table): bool => $config->getLimitedDocumentIds()->hasType($this->getTypeFromTable($table))
        );
    }

    /**
     * The "searchable" flag is not a column on the attribute itself but a per-input-screen
     * override on tl_metamodel_dcasetting (like "rte", "rows", etc.) - so it can differ between
     * screens/user groups for the very same attribute. The index built here is global, not
     * per-user, so an attribute counts as searchable if any published screen marks it so.
     *
     * @return array<int, string>
     */
    private function getSearchableColNames(IMetaModel $metaModel): array
    {
        $attributesById = [];
        foreach ($metaModel->getAttributes() as $attribute) {
            $attributesById[(int) $attribute->get('id')] = $attribute->getColName();
        }

        if ([] === $attributesById) {
            return [];
        }

        $searchableIds = $this->connection->createQueryBuilder()
            ->select('DISTINCT attr_id')
            ->from('tl_metamodel_dcasetting')
            ->where('searchable = :searchable')
            ->andWhere('published = :published')
            ->andWhere('attr_id IN (:attrIds)')
            ->setParameter('searchable', '1')
            ->setParameter('published', '1')
            ->setParameter('attrIds', array_keys($attributesById), ArrayParameterType::INTEGER)
            ->executeQuery()
            ->fetchFirstColumn()
        ;

        $colNames = [];
        foreach ($searchableIds as $attrId) {
            $colNames[] = $attributesById[(int) $attrId];
        }

        return $colNames;
    }

    /**
     * Collects the searchable text per (item, language) pair for a translated MetaModel - one
     * entry per language, not merged into a single one, so each translation gets its own hit
     * with its own edit link that opens straight into the matching language tab. This mirrors
     * how the existing FileUsageProviders for translated attributes (e.g.
     * AttributeTranslatedFileBundle\FileUsage\FileUsageProvider) build one result per language
     * rather than one result per item.
     *
     * Translated attribute values (translatedtext, translatedlongtext, ...) are not stored as
     * columns on the item's own table at all but in shared per-type side tables
     * (tl_metamodel_translatedtext and friends), keyed by the MetaModel's "current" language -
     * the same mechanism the edit view switches through its language tabs. Without selecting
     * each language explicitly here, only whatever language happens to be the active one (which,
     * off an HTTP request, is not well-defined at all) would end up in the index, silently
     * missing every other translation.
     *
     * A language other than the main one only gets its own entry for an item if at least one
     * searchable attribute actually has a value stored for that language - MetaModels silently
     * renders the main language's value instead when nothing was ever translated, and indexing
     * that fallback would just duplicate the main-language hit under every untranslated language
     * (see FallbackLanguageHintListener, which shows the same "Fallback" badge in the edit view
     * for the same reason).
     *
     * @param array<int, string> $ids
     * @param array<int, string> $searchableColNames
     *
     * @return array<string, array{itemId: string, language: string|null, values: array<int, string>}>
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function collectSearchableTexts(IMetaModel $metaModel, array $ids, array $searchableColNames): array
    {
        // Narrowed into its own variable rather than a boolean flag: Psalm cannot follow an
        // "instanceof" check back through an intermediate bool to know $metaModel is really an
        // ITranslatedMetaModel afterwards, so the translated-only methods below (getLanguages(),
        // selectLanguage(), ...) must be called on $translated, never on $metaModel directly.
        $translated   = $metaModel instanceof ITranslatedMetaModel ? $metaModel : null;
        $languages    = $translated?->getLanguages() ?? [null];
        $mainLanguage = $translated?->getMainLanguage();
        $originalLanguage = null;

        $entries = [];

        try {
            foreach ($languages as $language) {
                $idsForLanguage = $ids;

                if (null !== $translated && null !== $language) {
                    $previous          = $translated->selectLanguage($language);
                    $originalLanguage ??= $previous;

                    if ($language !== $mainLanguage) {
                        $idsForLanguage =
                            $this->filterIdsWithOwnTranslation($metaModel, $ids, $searchableColNames, $language);
                        if ([] === $idsForLanguage) {
                            continue;
                        }
                    }
                }

                foreach ($idsForLanguage as $id) {
                    // Wrapped per item: MetaModels attribute rendering can throw from all sorts of
                    // places for attribute types that assume an HTTP request is available (e.g.
                    // building absolute image URLs), which does not hold on the CLI/messenger
                    // worker. One such attribute, on one item, must not sink the whole reindex job.
                    try {
                        $item = $metaModel->findById($id);
                        if (null === $item) {
                            continue;
                        }

                        $values = [];
                        foreach ($searchableColNames as $colName) {
                            $value = (string) ($item->parseAttribute($colName, 'text')['text'] ?? '');
                            if ('' === $value) {
                                continue;
                            }
                            $values[] = trim(html_entity_decode(strip_tags($value), \ENT_QUOTES | \ENT_HTML5));
                        }

                        if ([] === $values) {
                            continue;
                        }

                        $docId           = null !== $language ? $id . '@' . $language : $id;
                        $entries[$docId] = ['itemId' => $id, 'language' => $language, 'values' => $values];
                    } catch (\Throwable) {
                        continue;
                    }
                }
            }
        } finally {
            if (null !== $translated && null !== $originalLanguage) {
                $translated->selectLanguage($originalLanguage);
            }
        }

        return $entries;
    }

    /**
     * Restricts $ids to those that have a genuine value in the given (non-main) language for at
     * least one of the searchable attributes - mirroring
     * FallbackLanguageHintListener::isFromFallback(), which uses the very same
     * ITranslated::getTranslatedDataFor() to decide whether the edit view shows a "Fallback"
     * badge on a field. Attributes that are not ITranslated at all (e.g. a plain "select") have
     * only one value regardless of language, so they never count toward "this language has
     * something of its own" and are skipped here.
     *
     * @param array<int, string> $ids
     * @param array<int, string> $searchableColNames
     *
     * @return array<int, string>
     */
    private function filterIdsWithOwnTranslation(
        IMetaModel $metaModel,
        array $ids,
        array $searchableColNames,
        string $language,
    ): array {
        $translatedIds = [];

        foreach ($searchableColNames as $colName) {
            $attribute = $metaModel->getAttribute($colName);
            if (!$attribute instanceof ITranslated) {
                continue;
            }

            foreach (array_keys($attribute->getTranslatedDataFor(array_values($ids), $language)) as $id) {
                $translatedIds[$id] = true;
            }
        }

        return array_values(array_intersect($ids, array_keys($translatedIds)));
    }

    private function buildTitle(IMetaModel $metaModel, string $id, string|null $language): string
    {
        $screen  = $this->viewCombination->getScreen($metaModel->getTableName());
        $pattern = (string) ($screen['meta']['subheadline'] ?? '');
        $label   = '' !== $pattern ? $this->renderLabel($metaModel, $id, $language, $pattern) : '';

        $title = $metaModel->getName() . ' › ' . ('' !== $label ? $label : $id);
        if (null !== $language) {
            $title .= ' (' . strtoupper($language) . ')';
        }

        return $title;
    }

    /**
     * Renders the subheadline label pattern against the item's own (language-aware) attribute
     * values rather than the raw database row: translated attributes have no column of their own
     * on the item's table at all (see collectSearchableTexts()), so a plain SQL row would leave
     * every translated token in the pattern empty.
     */
    private function renderLabel(IMetaModel $metaModel, string $id, string|null $language, string $pattern): string
    {
        $translated       = $metaModel instanceof ITranslatedMetaModel ? $metaModel : null;
        $originalLanguage = null;

        try {
            if (null !== $translated && null !== $language) {
                $originalLanguage = $translated->selectLanguage($language);
            }

            $item = $metaModel->findById($id);
            if (null === $item) {
                return '';
            }

            return $this->labelRenderer->render($pattern, $item->parseValue('text')['text'] ?? []);
        } catch (\Throwable) {
            return '';
        } finally {
            if (null !== $translated && null !== $originalLanguage) {
                $translated->selectLanguage($originalLanguage);
            }
        }
    }

    private function buildEditUrl(string $table, string $id, string|null $language): string
    {
        $this->refererId = $this->requestStack->getCurrentRequest()?->attributes->get('_contao_referer_id') ?? '';

        $params = [
            'tableName' => $table,
            'act'       => 'edit',
            'id'        => ModelId::fromValues($table, $id)->getSerialized(),
            'ref'       => $this->refererId,
            'rt'        => $this->csrfTokenManager->getToken($this->csrfTokenName)->getValue(),
        ];
        if (null !== $language) {
            $params['language'] = $language;
        }

        return $this->urlGenerator->generate('metamodels.metamodel', $params);
    }

    private function getTableFromDocument(Document $document): string
    {
        return $document->getMetadata()['table'] ?? '';
    }

    private function getTypeFromTable(string $table): string
    {
        return self::TYPE_PREFIX . $table;
    }
}
