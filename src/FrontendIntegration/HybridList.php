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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ondrej Brinkel <Sam256@web.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\FrontendTemplate;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\ItemList;

/**
 * Implementation of the MetaModel content element.
 *
 * @property FrontendTemplate $Template
 *
 * @deprecated We switched to fragments {@see ItemListController.php} in MetaModels 2.2. To be removed in MetaModels 3.
 *
 * @psalm-suppress DeprecatedClass
 * @psalm-suppress PropertyNotSetInConstructor
 */
class HybridList extends MetaModelHybrid
{
    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName = '### METAMODEL LIST ###';

    /**
     * Generate the list.
     *
     * @return string
     */
    public function generate()
    {
        // Fallback template.
        if (!empty($this->metamodel_layout)) {
            $this->strTemplate = $this->metamodel_layout;
        }

        /** @psalm-suppress DeprecatedClass */
        return parent::generate();
    }


    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param ItemList $objItemRenderer The list renderer instance to be used.
     *
     * @return array<string, string|list<string>>
     */
    protected function getFilterParameters($objItemRenderer)
    {
        $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
        assert($filterUrlBuilder instanceof FilterUrlBuilder);

        $filterUrl = $filterUrlBuilder->getCurrentFilterUrl();

        $result = [];
        foreach ($objItemRenderer->getFilterSettings()->getParameters() as $name) {
            if ($filterUrl->hasSlug($name)) {
                $value = $filterUrl->getSlug($name);
                assert(\is_string($value));
                $result[$name] = $value;
            } elseif ($filterUrl->hasGet($name)) {
                $value = $filterUrl->getGet($name);
                assert(\is_array($value) || \is_string($value));
                $result[$name] = $value;
            }
            // DAMN Contao - we have to "mark" the keys in the Input class as used as we get an 404 otherwise.
            Input::get($name);
        }

        return $result;
    }

    /**
     * Compile the content element.
     *
     * @return void
     */
    protected function compile()
    {
        $objItemRenderer = new ItemList();

        /**
         * @psalm-suppress UndefinedThisPropertyFetch
         * @psalm-suppress UndefinedMagicPropertyAssignment
         */
        $this->Template->searchable = !$this->metamodel_donotindex;

        /** @psalm-suppress UndefinedThisPropertyFetch */
        $sorting = $this->metamodel_sortby;
        /** @psalm-suppress UndefinedThisPropertyFetch */
        $direction = $this->metamodel_sortby_direction;
        if ($this->metamodel_sort_override) {
            if (\is_string($val = Input::get('orderBy'))) {
                $sorting = $val;
            }
            if (\is_string($val = Input::get('orderDir'))) {
                $direction = $val;
            }
        }

        /**
         * @psalm-suppress UndefinedThisPropertyFetch
         */
        $objItemRenderer
            ->setMetaModel($this->metamodel, $this->metamodel_rendersettings)
            ->setLimit($this->metamodel_use_limit, $this->metamodel_offset, $this->metamodel_limit)
            ->setPageBreak($this->perPage)
            ->setSorting($sorting, $direction)
            ->setFilterSettings($this->metamodel_filtering)
            ->setFilterParameters(
                StringUtil::deserialize($this->metamodel_filterparams, true),
                $this->getFilterParameters($objItemRenderer)
            )
            ->setMetaTags($this->metamodel_meta_title, $this->metamodel_meta_description);

        // Render items with encoded email strings as contao standard.
        /**
         * @psalm-suppress UndefinedThisPropertyFetch
         * @psalm-suppress UndefinedMagicPropertyAssignment
         */
        $this->Template->items         =
            StringUtil::encodeEmail($objItemRenderer->render($this->metamodel_noparsing, $this));
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $this->Template->numberOfItems = $objItemRenderer->getItems()->getCount();
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $this->Template->pagination    = $objItemRenderer->getPagination();
    }
}
