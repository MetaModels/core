<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use MetaModels\Filter\Setting\ICollection;

/**
 * FE-module for FE-filtering.
 *
 * @property \FrontendTemplate $Template
 */
class HybridFilterBlock extends MetaModelHybrid
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mm_filter_default';

    /**
     * The jumpTo page.
     *
     * @var array
     */
    private $arrJumpTo;

    /**
     * Get the jump to page data.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getJumpTo()
    {
        if (!isset($this->arrJumpTo)) {
            /** @var \Database\Result $page */
            $page = $GLOBALS['objPage'];
            $this->setJumpTo($page->row());

            if ($this->metamodel_jumpTo) {
                // Page to jump to when filter submit.
                $objPage = $this
                    ->getServiceContainer()
                    ->getDatabase()
                    ->prepare('SELECT id, alias FROM tl_page WHERE id=?')
                    ->limit(1)
                    ->execute($this->metamodel_jumpTo);

                if ($objPage->numRows) {
                    $this->setJumpTo($objPage->row());
                }
            }
        }

        return $this->arrJumpTo;
    }

    /**
     * Set the jump to page data.
     *
     * @param array $arrJumpTo The page data.
     *
     * @return HybridFilterBlock
     */
    public function setJumpTo($arrJumpTo)
    {
        $this->arrJumpTo = $arrJumpTo;

        return $this;
    }

    /**
     * Retrieve the filter collection.
     *
     * @return ICollection
     */
    public function getFilterCollection()
    {
        return $this
            ->getServiceContainer()
            ->getFilterFactory()
            ->createCollection($this->metamodel_filtering);
    }

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        // Get template if configured.
        if ($this->metamodel_fef_template) {
            $this->strTemplate = $this->metamodel_fef_template;
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     *
     * @return void
     */
    protected function compile()
    {
        $objFilter = new FrontendFilter();
        $arrFilter = $objFilter->getMetaModelFrontendFilter($this);

        $this->Template->setData(array_merge($this->Template->getData(), $arrFilter));
        $this->Template->submit = $arrFilter['submit'];
    }
}
