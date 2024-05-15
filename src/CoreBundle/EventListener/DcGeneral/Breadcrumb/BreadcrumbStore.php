<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use Contao\StringUtil;
use MetaModels\CoreBundle\Assets\IconBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_key_exists;
use function str_replace;
use function str_starts_with;
use function ucfirst;

/**
 * The breadcrumb store.
 */
class BreadcrumbStore
{
    /**
     * The elements.
     *
     * @var array
     */
    private array $elements = [];

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private IconBuilder $iconBuilder;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * List of "current" ids.
     *
     * @var string[]
     */
    private array $idList = [];

    /**
     * The current URI.
     *
     * @var string
     */
    private string $uri;

    /**
     * Create a new instance.
     *
     * @param IconBuilder         $iconBuilder The icon builder.
     * @param TranslatorInterface $translator  The translator.
     * @param string              $uri         The current URI.
     */
    public function __construct(IconBuilder $iconBuilder, TranslatorInterface $translator, $uri)
    {
        $this->iconBuilder = $iconBuilder;
        $this->translator  = $translator;
        $this->uri         = $uri;
    }

    /**
     * Push an entry.
     *
     * @param string $url   The url.
     * @param string $table The table.
     * @param string $icon  The icon.
     *
     * @return void
     */
    public function push($url, $table, $icon)
    {
        $this->elements[] = [
            'url' => $url,
            'text' => $this->getLabel($table),
            'icon' => $this->iconBuilder->getBackendIcon($icon)
        ];
    }

    /**
     * Test if an id has been set.
     *
     * @param string $table The table name.
     *
     * @return bool
     */
    public function hasId($table): bool
    {
        return array_key_exists($table, $this->idList);
    }

    /**
     * Set an id.
     *
     * @param string $table  The table name.
     * @param string $itemId The id.
     *
     * @return void
     */
    public function setId($table, $itemId)
    {
        $this->idList[$table] = $itemId;
    }

    /**
     * Get an id.
     *
     * @param string $table The table name.
     *
     * @return null|string
     */
    public function getId($table)
    {
        return $this->hasId($table) ? $this->idList[$table] : null;
    }

    /**
     * Retrieve uri.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get for a table the human-readable name or a fallback.
     *
     * @param string $table Name of table.
     *
     * @return string The human-readable name.
     */
    public function getLabel($table): string
    {
        if (!str_starts_with($table, 'tl_')) {
            return $table;
        }
        $shortTable = str_replace('tl_', '', $table);
        $label      = $this->translator->trans($shortTable, [], 'metamodels_navigation');
        if ($label === $shortTable) {
            $shortTable = str_replace('tl_metamodel_', '', $table);
            return ucfirst($shortTable) . ' %s';
        }

        return StringUtil::specialchars($label);
    }

    /**
     * Retrieve the elements.
     *
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}
