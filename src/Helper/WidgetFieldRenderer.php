<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Helper;

use Contao\CoreBundle\Twig\Interop\ContextFactory;
use Contao\Widget;
use Twig\Environment;

/**
 * Renders just the "field" part of a form widget - the bare input markup, without the label or
 * the surrounding form-row wrapper.
 *
 * Two different kinds of widget are in play here, and they need two different approaches:
 *
 * - Native Contao widgets (Contao\FormText and siblings) no longer override generate() in
 *   Contao 6 - it now unconditionally throws "Frontend form widgets cannot be generated. Use
 *   "...::parse()" instead.". Every one of their templates (form_text.html.twig and siblings)
 *   defines the input markup in a dedicated "field" Twig block, with the label in a separate
 *   "label" block, both included by the "form_row.html.twig" base template that Widget::parse()
 *   renders in full. This renders just the "field" block directly - using the same
 *   context-building service Contao's own Widget::inherit() uses internally - to get the bare
 *   input markup a caller building its own label/field layout (MetaModels' frontend filter
 *   widgets among them) actually needs.
 * - MetaModels' own widgets (MultiTextWidget and siblings) still override generate() themselves
 *   with a classic PHP string builder, entirely unaffected by the Contao 6 change - calling it
 *   directly is correct and unavoidable for these, since their Twig templates (form_tags.html.twig
 *   and siblings) follow the backend "be_widget.html.twig" convention instead (a single combined
 *   render, no separate "field"/"label" blocks to pick apart).
 */
final class WidgetFieldRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ContextFactory $contextFactory,
    ) {
    }

    public function renderField(Widget $widget): string
    {
        if (Widget::class !== (new \ReflectionMethod($widget, 'generate'))->getDeclaringClass()->getName()) {
            return $widget->generate();
        }

        // $widget->template (Contao's own magic property, backed by $strTemplate) cannot be
        // trusted here: MetaModels' own DCA "eval.template" option - the name of the *surrounding*
        // filter-item wrapper template, unrelated to this widget's own rendering - shares the
        // exact same property name and gets flattened onto the widget's attributes on
        // construction, clobbering whatever the widget class itself set as its default template
        // (e.g. "form_text" on Contao\FormText). That collision was harmless under the old
        // generate()-based rendering, which never consulted $strTemplate for a frontend widget,
        // but matters now. The class's own pristine default - never touched by DCA attributes -
        // is the only reliable source for "which Twig template does this widget actually render".
        $defaults = (new \ReflectionClass($widget))->getDefaultProperties();
        $templateName = $defaults['strTemplate'] ?? $widget->template;

        $template = $this->twig->load('@Contao/' . $templateName . '.html.twig');

        return $template->renderBlock('field', $this->contextFactory->fromClass($widget));
    }
}
