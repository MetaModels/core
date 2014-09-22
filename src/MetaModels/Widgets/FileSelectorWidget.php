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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Widgets;

use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Provide methods to handle input field "file tree".
 */
class FileSelectorWidget extends \FileSelector
{
    /**
     * Initialize the object.
     *
     * @param array     $arrAttributes An optional attributes array.
     *
     * @param DcGeneral $objDca        Optionally the data container instance
     *                                 (Removed for Contao 3.3 in there we have $arrAttributes['dataContainer']).
     *
     * @throws DcGeneralInvalidArgumentException When the property could not be retrieved from the DcGeneral.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function __construct($arrAttributes = null, $objDca = null)
    {
        parent::__construct($arrAttributes, $objDca);

        if (!$this->strField) {
            $this->strField = $arrAttributes['name'];
        }

        // Strip the leading "ctrl_" from the field name if present.
        if (substr($this->strField, 0, 5) === 'ctrl_') {
            $chunks = explode('_', $this->strField);
            array_shift($chunks);
            $this->strField = implode('_', $chunks);
        }

        if (version_compare(VERSION, '3.3', '<')) {
            // Pre Contao 3.3 - we get the objDca as second parameter here.
            if (!isset($this->objDca) && $objDca) {
                $this->objDca = $objDca;
            }

            // Compatibility with Contao pre 3.3 which utilizes the $GLOBALS array directly.
            if (!isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField])) {

                $environment         = $this->objDca->getEnvironment();
                $propertyDefinitions = $environment->getDataDefinition()->getPropertiesDefinition();

                if (!$propertyDefinitions->hasProperty($this->strField)) {
                    throw new DcGeneralInvalidArgumentException(
                        'Property ' . $this->strField . ' is not defined in propertyDefinitions.'
                    );
                }

                $propInfo  = $propertyDefinitions->getProperty($this->strField);
                $propExtra = $propInfo->getExtra();

                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField] = array('eval' => $propExtra);
            }
        }
    }

    /**
     * Generate a particular sub part of the file tree and return it as HTML string.
     *
     * @param string $folder   The folder name.
     *
     * @param string $strField The property name.
     *
     * @param int    $level    The level where the given folder shall be rendered within.
     *
     * @param bool   $mount    Flag determining if the passed folder shall be handled as root level
     *                         (optional, default: no).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generateAjax($folder, $strField, $level, $mount = false)
    {
        return parent::generateAjax($folder, $this->strField, $level, $mount);
    }
}
