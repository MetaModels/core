<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\System;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * FE-module for FE-filtering.
 *
 * @property \FrontendTemplate $Template
 * @property string            $metamodel_jumpTo
 * @property string            $metamodel_fef_template
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
     * The database connection.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

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
                $statement = $this->getConnection()->prepare('SELECT id, alias FROM tl_page WHERE id=? LIMIT 0,1');
                $statement->bindValue(1, $this->metamodel_jumpTo);
                $statement->execute();

                if ($statement->rowCount()) {
                    $this->setJumpTo($statement->fetch(\PDO::FETCH_ASSOC));
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
        $objFilter = new FrontendFilter($this->getConnection());
        $arrFilter = $objFilter->getMetaModelFrontendFilter($this);

        $this->Template->setData(array_merge($this->Template->getData(), $arrFilter));
        $this->Template->submit = $arrFilter['submit'];
    }

    /**
     * Obtain the filter setting factory.
     *
     * @return IFilterSettingFactory
     */
    private function getFilterFactory(): IFilterSettingFactory
    {
        if (null === $this->filterFactory) {
            return $this->filterFactory = System::getContainer()->get('metamodels.filter_setting_factory');
        }

        return $this->filterFactory;
    }
}
