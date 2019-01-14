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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Widgets;

use Contao\Widget;

/**
 * Form field with more than 1 input, based on form field by Leo Feyer.
 */
class MultiTextWidget extends Widget
{
    /**
     * Submit user input.
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * The template to use.
     *
     * @var string
     */
    protected $strTemplate = 'form_widget';

    /**
     * Add specific attributes.
     *
     * @param string $strKey   Name of the key to set.
     *
     * @param mixed  $varValue The value to use.
     *
     * @return void
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'maxlength':
                if ($varValue > 0) {
                    $this->arrAttributes['maxlength'] = $varValue;
                }
                break;

            case 'mandatory':
                if ($varValue) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
                parent::__set($strKey, $varValue);
                break;

            case 'placeholder':
                $this->arrAttributes['placeholder'] = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Trim the values and validate them.
     *
     * @param mixed $varInput The value to process.
     *
     * @return mixed The processed value
     */
    protected function validator($varInput)
    {
        if (is_array($varInput)) {
            $value = array();
            foreach ($varInput as $key => $input) {
                $value[$key] = parent::validator($input);
            }

            return $value;
        }

        return parent::validator(trim($varInput));
    }


    /**
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        $return = '';
        for ($i = 0; $i < $this->size; $i++) {
            $return .= sprintf(
                '<input type="%s" name="%s[]" id="ctrl_%s_%s" class="text%s%s" value="%s"%s%s',
                'text',
                $this->strName,
                $this->strId,
                $i,
                '',
                (strlen($this->strClass) ? ' ' . $this->strClass : ''),
                specialchars($this->varValue[$i]),
                $this->getAttributes(),
                $this->strTagEnding
            );
        }

        return $return;
    }
}
