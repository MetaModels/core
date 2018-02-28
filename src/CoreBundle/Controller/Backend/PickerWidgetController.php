<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Contao\Backend;
use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class renders the picker widget.
 */
class PickerWidgetController
{
    /**
     * The twig engine.
     *
     * @var EngineInterface
     */
    private $templating;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param EngineInterface     $templating The twig engine.
     * @param TranslatorInterface $translator The translator.
     */
    public function __construct(EngineInterface $templating, TranslatorInterface $translator)
    {
        $this->templating = $templating;
        $this->translator = $translator;
    }

    /**
     * Render the picker.
     *
     * @return Response
     *
     * @throws \RuntimeException Throw field parameter error.
     */
    public function __invoke()
    {
        Controller::loadLanguageFile('default');
        Controller::loadLanguageFile('modules');

        Input::setGet('popup', true);

        $inputName = Input::get('fld');
        if (!preg_match('~^[a-z\-_0-9]+$~i', $inputName)) {
            throw new RuntimeException('Field-Parameter ERROR!');
        }

        $styleSheets = [];
        $javaScripts = [];
        if ('panelLayout' === $inputName) {
            // $styleSheets[] = 'bundles/metamodelscore/css/';
            $javaScripts[] = 'bundles/metamodelscore/js/panelpicker.js';
        } elseif ('tl_class' === $inputName) {
            // $styleSheets[] = 'bundles/metamodelscore/css/';
            $javaScripts[] = 'bundles/metamodelscore/js/stylepicker.js';
        }
        // FIXME: can be removed when https://github.com/contao/core-bundle/pull/1153 is merged.
        foreach ($styleSheets as $styleSheet) {
            $GLOBALS['TL_CSS'][] = $styleSheet;
        }
        foreach ($javaScripts as $javaScript) {
            $GLOBALS['TL_JAVASCRIPT'][] = $javaScript;
        }

        $items  = [];
        $prefix = 'MSC.' . $inputName . '.';
        foreach ($GLOBALS[Input::get('item')] as $item) {
            $label = $this->translator->trans($prefix . $item['cssclass'], [], 'contao_default');
            $descr = '';
            if (is_array($label)) {
                $descr = $label[1];
                $label = $label[0];
            }
            $items[] = [
                'label'    => $label,
                'descr'    => $descr,
                'cssclass' => $item['cssclass'],
            ];
        }

        return new Response(
            $this->templating->render(
                'MetaModelsCoreBundle:Backend:be_dcastylepicker.html.twig',
                [
                    'language'    => $GLOBALS['TL_LANGUAGE'],
                    'charset'     => $GLOBALS['TL_CONFIG']['characterSet'],
                    'base'        => Environment::get('base'),
                    'assets_url'  => TL_ASSETS_URL,
                    'theme'       => Backend::getTheme(),
                    'items'       => $items,
                    'field'       => Input::get('inputName'),
                    'stylesheets' => $styleSheets,
                    'javascripts' => $javaScripts,
                    'headline'    => $this->translator->trans('MSC.metamodelspicker', [], 'contao_default'),
                    'noItems'     => $this->translator->trans('MSC.metamodelspicker_noItems', [], 'contao_default'),
                ]
            )
        );
    }
}
