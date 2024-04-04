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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\Database\Result;
use Contao\FrontendTemplate;
use Contao\System;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * FE-module for FE-filtering.
 *
 * @property FrontendTemplate $Template
 * @property string           $metamodel_jumpTo
 * @property string           $metamodel_fef_template
 * @property bool             $metamodel_fef_autosubmit
 * @property bool             $metamodel_fef_hideclearfilter
 * @property bool             $metamodel_available_values
 * @property string           $metamodel_fef_params
 * @property string           $metamodel_fef_urlfragment
 * @property int              $id
 *
 * @psalm-suppress DeprecatedClass
 * @psalm-suppress PropertyNotSetInConstructor
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
     * @var array|null
     */
    private ?array $arrJumpTo = null;

    /**
     * The database connection.
     *
     * @var IFilterSettingFactory|null
     */
    private ?IFilterSettingFactory $filterFactory = null;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder|null
     */
    private ?FilterUrlBuilder $filterUrlBuilder = null;

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
        if (null === $this->arrJumpTo) {
            if ($this->metamodel_jumpTo) {
                // Page to jump to when filter submit.
                $statement = $this->getConnection()
                    ->createQueryBuilder()
                    ->select('t.id', 't.alias')
                    ->from('tl_page', 't')
                    ->where('t.id=:id')
                    ->setParameter('id', $this->metamodel_jumpTo)
                    ->setMaxResults(1)
                    ->executeQuery();

                if (false !== ($row = $statement->fetchAssociative())) {
                    $this->arrJumpTo = $row;

                    return $this->arrJumpTo;
                }
            }

            /** @var Result $page */
            $page = $GLOBALS['objPage'];
            $this->arrJumpTo = $page->row();
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
        /** @psalm-suppress UndefinedThisPropertyFetch */
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

        /** @psalm-suppress DeprecatedClass */
        return parent::generate();
    }

    /**
     * Generate the module.
     *
     * @return void
     */
    protected function compile()
    {
        $objFilter = new FrontendFilter($this->getConnection(), $this->getFilterUrlBuilder());
        $arrFilter = $objFilter->getMetaModelFrontendFilter($this);

        $this->Template->setData(\array_merge($this->Template->getData(), $arrFilter));
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
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
            $filterSettingFactory = System::getContainer()->get('metamodels.filter_setting_factory');
            assert($filterSettingFactory instanceof IFilterSettingFactory);

            return $this->filterFactory = $filterSettingFactory;
        }

        return $this->filterFactory;
    }

    /**
     * Obtain the filter URL builder.
     *
     * @return FilterUrlBuilder
     */
    private function getFilterUrlBuilder(): FilterUrlBuilder
    {
        if (null === $this->filterUrlBuilder) {
            $filterUrl = System::getContainer()->get('metamodels.filter_url');
            assert($filterUrl instanceof FilterUrlBuilder);

            return $this->filterUrlBuilder = $filterUrl;
        }

        return $this->filterUrlBuilder;
    }
}
