<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
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
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use MetaModels\ItemList;

/**
 * Implementation of the MetaModel content element.
 *
 * @property \FrontendTemplate $Template
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

        return parent::generate();
    }


    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param ItemList $objItemRenderer The list renderer instance to be used.
     *
     * @return string[]
     */
    protected function getFilterParameters($objItemRenderer)
    {
        $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
        $filterUrl        = $filterUrlBuilder->getCurrentFilterUrl();

        $result = [];
        foreach ($objItemRenderer->getFilterSettings()->getParameters() as $name) {
            if ($filterUrl->hasSlug($name)) {
                $result[$name] = $filterUrl->getSlug($name);
            } elseif ($filterUrl->hasGet($name)) {
                $result[$name] = $filterUrl->getGet($name);
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

        $this->Template->searchable = !$this->metamodel_donotindex;

        $sorting   = $this->metamodel_sortby;
        $direction = $this->metamodel_sortby_direction;
        if ($this->metamodel_sort_override) {
            if (\Input::get('orderBy')) {
                $sorting = \Input::get('orderBy');
            }
            if (\Input::get('orderDir')) {
                $direction = \Input::get('orderDir');
            }
        }

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
        $this->Template->items         =
            \StringUtil::encodeEmail($objItemRenderer->render($this->metamodel_noparsing, $this));
        $this->Template->numberOfItems = $objItemRenderer->getItems()->getCount();
        $this->Template->pagination    = $objItemRenderer->getPagination();
    }
}
