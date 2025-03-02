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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Widgets;

use Contao\FileSelector;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Provide methods to handle input field "file tree".
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress UndefinedClass
 */
class FileSelectorWidget extends FileSelector
{
    /**
     * Initialize the object.
     *
     * @param array $arrAttributes An optional attributes array.
     *
     * @throws DcGeneralInvalidArgumentException When the property could not be retrieved from the DcGeneral.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function __construct($arrAttributes = null)
    {
        /** @psalm-suppress DeprecatedClass */
        parent::__construct($arrAttributes);

        if (!$this->strField) {
            $this->strField = $arrAttributes['name'] ?? '';
        }

        // Strip the leading "ctrl_" from the field name if present.
        if (\str_starts_with($this->strField ?? '', 'ctrl_')) {
            $chunks = \explode('_', $this->strField ?? '');
            \array_shift($chunks);
            $this->strField = \implode('_', $chunks);
        }
    }

    /**
     * Generate a particular sub part of the file tree and return it as HTML string.
     *
     * @param string $strFolder The folder name.
     * @param string $strField  The property name.
     * @param int    $level     The level where the given folder shall be rendered within.
     * @param bool   $mount     Flag determining if the passed folder shall be handled as root level
     *                          (optional, default: no).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @psalm-suppress ImplementedParamTypeMismatch
     */
    public function generateAjax($strFolder, $strField, $level, $mount = false)
    {
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress DeprecatedClass
         */
        return parent::generateAjax($strFolder, $this->strField, $level, $mount);
    }
}
