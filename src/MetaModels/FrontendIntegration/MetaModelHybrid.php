<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\FormModel;
use Contao\Hybrid;
use Contao\ModuleModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Base implementation of a MetaModel Hybrid element.
 */
abstract class MetaModelHybrid extends Hybrid
{
    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName;

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink;

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix;

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }

    /**
     * Create a new instance.
     *
     * @param ContentModel|ModuleModel|FormModel $objElement The object from the database.
     *
     * @param string                             $strColumn  The column the element is displayed within.
     */
    public function __construct($objElement, $strColumn = 'main')
    {
        parent::__construct($objElement, $strColumn);

        $this->arrData = $objElement->row();
        // Get space and CSS ID from the parent element (!)
        $this->space      = deserialize($objElement->space);
        $this->cssID      = deserialize($objElement->cssID, true);
        $this->typePrefix = $objElement->typePrefix;
        $this->strKey     = $objElement->type;
        $arrHeadline      = deserialize($objElement->headline);
        $this->headline   = is_array($arrHeadline) ? $arrHeadline['value'] : $arrHeadline;
        $this->hl         = is_array($arrHeadline) ? $arrHeadline['unit'] : 'h1';
    }

    /**
     * Generate the list.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {

            $strInfo = '';
            if ($this->metamodel) {
                // Add CSS file.
                $GLOBALS['TL_CSS'][] = 'system/modules/metamodels/assets/css/style.css';

                // Retrieve name of MetaModels.
                $factory       = $this->getServiceContainer()->getFactory();
                $metaModelName = $factory->translateIdToMetaModelName($this->metamodel);
                $metaModel     = $factory->getMetaModel($metaModelName);
                $strInfo       = '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="MetaModel">MM:</abbr> </span>' . $metaModel->getName() . '</div>';

                $database = \Database::getInstance();

                // Retrieve name of filter.
                if ($this->metamodel_filtering) {
                    $infoFi = $database
                        ->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
                        ->execute($this->metamodel_filtering)
                        ->row();
                    $strInfo .= '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="Filter">Fi:</abbr> </span>' . $infoFi['name'] . '</div>';
                }

                // Retrieve name of rendersetting.
                if ($this->metamodel_rendersettings) {
                    $infoRs = $database
                        ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
                        ->execute($this->metamodel_rendersettings)
                        ->row();
                    $strInfo .= '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="Render-Setting">Rs:</abbr> </span>' . $infoRs['name'] . '</div>';
                }
            }

            $objTemplate           = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = $this->wildCardName . $strInfo;
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = ($this->typePrefix == 'mod_'? 'FE-Modul: ' : '').$this->name;
            $objTemplate->href     = sprintf($this->wildCardLink, $this->id);

            return $objTemplate->parse();
        }

        return parent::generate();
    }
}
