<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Database\Result;
use MetaModels\Attribute\Factory as AttributeFactory;
use MetaModels\Factory as ModelFactory;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Filter\IFilter;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * This is the IMetaModelFilterSettings reference implementation.
 *
 * @package       MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Collection implements ICollection
{
    /**
     * The additional meta data for this filter setting collection.
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * The filter settings contained.
     *
     * @var ISimple[]
     */
    protected $arrSettings = array();

    /**
     * The attached MetaModel.
     *
     * @var IMetaModel
     */
     protected $metaModel;

    /**
     * Create a new instance.
     *
     * @param array $arrData The meta data for this filter setting collection.
     */
    public function __construct($arrData)
    {
        $this->arrData = $arrData;
    }

    /**
     * Create a new setting.
     *
     * @param Result $objSettings The information from which to initialize the setting from.
     *
     * @return ISimple
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function newSetting($objSettings)
    {
        $strClass = $GLOBALS['METAMODELS']['filters'][$objSettings->type]['class'];
        // TODO: add factory support here.
        if ($strClass) {
            return new $strClass($this, $objSettings->row());
        }
        return null;
    }

    /**
     * Fetch all child rules for the given setting.
     *
     * @param Result        $objBaseSettings The database information of the parent setting.
     *
     * @param IWithChildren $objSetting      The information from which to initialize the setting from.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function collectRulesFor($objBaseSettings, $objSetting)
    {
        $objDB       = \Database::getInstance();
        $objSettings = $objDB
            ->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE pid=? AND enabled=1 ORDER BY sorting ASC')
            ->execute($objBaseSettings->id);

        while ($objSettings->next()) {
            $objNewSetting = $this->newSetting($objSettings);
            if ($objNewSetting) {
                $objSetting->addChild($objNewSetting);
                // Collect next level.
                if (!empty($GLOBALS['METAMODELS']['filters'][$objNewSetting->get('type')]['nestingAllowed'])) {
                    /** @var IWithChildren $objNewSetting */
                    $this->collectRulesFor($objSettings, $objNewSetting);
                }
            }
        }
    }

    /**
     * Set the MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return Collection
     */
    public function setMetaModel($metaModel)
    {
        $this->metaModel = $metaModel;

        return $this;
    }

    /**
     * Retrieve the MetaModel this filter belongs to.
     *
     * @return IMetaModel
     *
     * @throws \RuntimeException When the MetaModel can not be determined.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaModel()
    {
        if ($this->metaModel) {
            return $this->metaModel;
        }

        if (!$this->arrData['pid']) {
            throw new \RuntimeException(
                sprintf(
                    'Error: Filtersetting %d not attached to a MetaModel',
                    $this->arrData['id']
                )
            );
        }

        $dispatcher = $GLOBALS['container']['event-dispatcher'];
        $factory    = new ModelFactory($dispatcher, new AttributeFactory($dispatcher));
        $model      = $factory->getMetaModel($factory->translateIdToMetaModelName($this->arrData['pid']));

        $this->setMetaModel($model);

        return $this->metaModel;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException When the filter setting is not created from a database result (holds no id).
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function collectRules()
    {
        if (!$this->arrData['id']) {
            throw new \RuntimeException(
                'Error: dynamically created FilterSettings can not collect attribute information',
                1
            );
        }

        $objDB       = \Database::getInstance();
        $objSettings = $objDB
            ->prepare(
                'SELECT * FROM tl_metamodel_filtersetting WHERE fid=? AND pid=0 AND enabled=1 ORDER BY sorting ASC'
            )
            ->execute($this->arrData['id']);

        while ($objSettings->next()) {
            $objNewSetting = $this->newSetting($objSettings);
            if ($objNewSetting) {
                $this->arrSettings[] = $objNewSetting;
                if (!empty($GLOBALS['METAMODELS']['filters'][$objNewSetting->get('type')]['nestingAllowed'])) {
                    /** @var IWithChildren $objNewSetting */
                    $this->collectRulesFor($objSettings, $objNewSetting);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRules(IFilter $objFilter, $arrFilterUrl, $arrIgnoredFilter = array())
    {
        foreach ($this->arrSettings as $objSetting) {
            // If the setting is on the ignore list skip it.
            if (in_array($objSetting->get('id'), $arrIgnoredFilter)) {
                continue;
            }

            $objSetting->prepareRules($objFilter, $arrFilterUrl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        $arrFilterUrl = array();
        foreach ($this->arrSettings as $objSetting) {
            $arrFilterUrl = array_merge($arrFilterUrl, $objSetting->generateFilterUrlFrom($objItem, $objRenderSetting));
        }
        return $arrFilterUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $arrParams = array();
        foreach ($this->arrSettings as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameters());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        $arrParams = array();
        foreach ($this->arrSettings as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameterDCA());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        $arrParams = array();

        foreach ($this->arrSettings as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameterFilterNames());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterWidgets(
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        $arrParams = array();

        // Get the id with all enabled filter.
        $objFilter = $this->getMetaModel()->getEmptyFilter();
        $this->addRules($objFilter, $arrFilterUrl);

        $arrBaseIds = $objFilter->getMatchingIds();

        foreach ($this->arrSettings as $objSetting) {
            if ($objSetting->get('skipfilteroptions')) {
                $objFilter = $this->getMetaModel()->getEmptyFilter();
                $this->addRules($objFilter, $arrFilterUrl, array($objSetting->get('id')));
                $arrIds = $objFilter->getMatchingIds();
            } else {
                $arrIds = $arrBaseIds;
            }

            $arrParams = array_merge(
                $arrParams,
                $objSetting->getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $objFrontendFilterOptions)
            );
        }

        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        $arrAttributes = array();

        foreach ($this->arrSettings as $objSetting) {
            $arrAttributes = array_merge($arrAttributes, $objSetting->getReferencedAttributes());
        }

        return $arrAttributes;
    }
}
