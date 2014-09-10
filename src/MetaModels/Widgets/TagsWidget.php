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
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Widgets;

/**
 * Form field "tags", based on form field by Leo Feyer
 *
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class TagsWidget extends \Widget
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'form_widget';


    /**
     * {@inheritDoc}
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'maxlength':
                if ($varValue > 0) {
                    $this->arrAttributes['maxlength'] =  $varValue;
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
     * {@inheritDoc}
     */
    protected function validator($varInput)
    {
        if (is_array($varInput)) {
            return parent::validator($varInput);
        }

        return parent::validator(trim($varInput));
    }


    protected function generateOption($val, $count)
    {
        // If true we need another offset.
        $intSub = ($this->arrConfiguration['includeBlankOption'] ? -1 : 1);

        $strClass  = $this->strName;
        $strClass .= ($count == 0) ? ' first' : '';
        $strClass .= ($count == count($this->options) - $intSub ) ? ' last' : '';
        $strClass .= ($count % 2 == 1) ? ' even' : ' odd';
        $strClass .= (strlen($this->strClass)) ? ' ' . $this->strClass : '';

        return sprintf(
            '<span class="%1$s opt_%2$s">' .
            '<input type="checkbox" name="%8$s[]" id="opt_%3$s" class="checkbox" value="%4$s"%5$s%6$s ' .
            '<label id="lbl_%3$s" for="opt_%3$s">%7$s</label></span>',
            // @codingStandardsIgnoreStart - Keep the comments.
            $strClass,                                                                                              // 1
            $count,                                                                                                 // 2
            $this->strName.'_'.$count,                                                                              // 3
            $val['value'],                                                                                          // 4
            (is_array($this->varValue) ? (in_array($val['value'],$this->varValue) ? ' checked="checked"' : ''):''), // 5
            $this->getAttributes() . $this->strTagEnding,                                                           // 6
            $val['label'],                                                                                          // 7
            $this->strName                                                                                          // 8
            // @codingStandardsIgnoreEnd
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        $return = sprintf(
            '<fieldset id="ctrl_%s" class="checkbox_container">
',
            $this->strName
        );

        $count = 0;

        if ($this->options && is_array($this->options)) {
            if ($this->arrConfiguration['includeBlankOption']) {
                $return .= $this->generateOption(
                    array('value' => '--none--', 'label' => $this->arrConfiguration['blankOptionLabel']),
                    $count++
                );
            }

            // Select all tags.
            // TODO: does this really make sense? do we need such an option?
            $return .= $this->generateOption(
                array('value' => '--all--', 'label' => $GLOBALS['TL_LANG']['metamodels_frontendfilter']['select_all']),
                $count++
            );

            foreach ($this->options as $key => $val) {
                $return .= $this->generateOption($val, $count++);
            }
        } else {
            // Do not filter.
            if ($this->arrConfiguration['includeBlankOption']) {
                $return .= $this->generateOption(
                    array
                    (
                        'value' => '',
                        'label' => $this->arrConfiguration['blankOptionLabel'] .
                            '<span>' .
                            $GLOBALS['TL_LANG']['metamodels_frontendfilter']['no_combinations'] .
                            '</span>'
                    ),
                    $count++
                );
            }
        }

        $return .= '</fieldset>';

        return $return;
    }
}
