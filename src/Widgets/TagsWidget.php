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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Widgets;

use Contao\System;
use Contao\Widget;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form field "tags", based on form field by Leo Feyer.
 *
 * @psalm-type TTagOption=array{value: string, label: string}
 *
 * @property list<TTagOption>|null $options
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TagsWidget extends Widget
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'form_tags';

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    protected function validator($varInput)
    {
        if (\is_array($varInput)) {
            return parent::validator($varInput);
        }

        return parent::validator(\trim((string) ($varInput ?? '')));
    }

    /**
     * Get the css class for an option.
     *
     * @param int $index The sequence number of the current option.
     *
     * @return string
     */
    protected function getClassForOption(int $index): string
    {
        // If true we need another offset.
        $intSub   = ($this->arrConfiguration['includeBlankOption'] ? 1 : 0);
        $intSub  += ((isset($this->arrConfiguration['showSelectAll'])
                      && null !== $this->arrConfiguration['showSelectAll']) ? 0 : 1);
        $strClass = $this->strName;

        if ($index === 0) {
            $strClass .= ' first';
        } elseif ($index === (\count($this->options ?? []) - 1 + $intSub)) {
            $strClass .= ' last';
        }

        if (($index % 2) === 1) {
            $strClass .= ' even';
        } else {
            $strClass .= ' odd';
        }

        return ((\strlen($this->strClass)) ? $this->strClass . ' ' : '') . $strClass;
    }

    /**
     * Generate a single checkbox.
     *
     * @param TTagOption $val   The value array (needs keys "value" and "label").
     * @param int        $index The sequence number of this option (used for even/odd determination).
     *
     * @return string
     */
    protected function generateOption(array $val, int $index): string
    {
        $checked = '';
        if (\is_array($this->varValue) && \in_array($val['value'], $this->varValue)) {
            $checked = ' checked="checked"';
        }

        return \sprintf(
            '<span class="%1$s opt_%2$s">' .
            '<input type="checkbox" name="%8$s[]" id="opt_%3$s" class="checkbox" value="%4$s"%5$s%6$s ' .
            '<label id="lbl_%3$s" for="opt_%3$s">%7$s</label></span>',
            // @codingStandardsIgnoreStart - Keep the comments.
            $this->getClassForOption($index),     // 1
            $index,                                       // 2
            $this->strName . '_' . $index,                // 3
            $val['value'],                                // 4
            $checked,                                     // 5
            $this->getAttributes() . $this->strTagEnding, // 6
            $val['label'],                                // 7
            $this->strName                                // 8
            // @codingStandardsIgnoreEnd
        );
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generate(): string
    {
        $return = \sprintf(
            '<fieldset id="ctrl_%s" class="checkbox_container">
',
            $this->strName
        );

        $count = 0;

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        if (\is_array($this->options) && [] !== $this->options) {
            // Show not filter option.
            if ((bool) ($this->arrConfiguration['includeBlankOption'] ?? false)) {
                $return .= $this->generateOption(
                    [
                        'value' => '--none--',
                        'label' => (string) ($this->arrConfiguration['blankOptionLabel'] ?? '')
                    ],
                    $count++
                );
            }

            // Show select all checkbox - check null as BC-Layer.
            if ((bool) ($this->arrConfiguration['showSelectAll'] ?? false)) {
                $return .= $this->generateOption(
                    [
                        'value' => '--all--',
                        'label' => $translator->trans('select_all', [], 'metamodels_filter')
                    ],
                    $count++
                );
            }

            foreach ($this->options as $val) {
                $return .= $this->generateOption($val, $count++);
            }
        } else {
            $return .= '<span>' . $translator->trans('no_combinations', [], 'metamodels_filter') . '</span>';
        }

        $return .= '</fieldset>';

        return $return;
    }
}
