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

use Contao\Input;
use Symfony\Component\HttpFoundation\Request;
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
     * The configuration for the pickers.
     *
     * @var array
     */
    private $configuration;

    /**
     * Create a new instance.
     *
     * @param EngineInterface     $templating    The twig engine.
     * @param TranslatorInterface $translator    The translator.
     * @param array               $configuration The picker configuration.
     */
    public function __construct(EngineInterface $templating, TranslatorInterface $translator, array $configuration)
    {
        $this->templating    = $templating;
        $this->translator    = $translator;
        $this->configuration = $configuration;
    }

    /**
     * Render the picker.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws \RuntimeException Throw field parameter error.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function __invoke(Request $request)
    {
        // Sets be_main.html5 to popup mode in contao/core-bundle/src/Resources/contao/controllers/BackendMain.php.
        Input::setGet('popup', true);

        $inputName = $request->get('fld');
        if (!preg_match('~^[a-z\-_0-9]+$~i', $inputName)) {
            throw new \RuntimeException('Field-Parameter ERROR!');
        }

        $styleSheets = [];
        $javaScripts = [];
        if ('panelLayout' === $inputName) {
            $javaScripts[] = 'bundles/metamodelscore/js/panelpicker.js';
        } elseif ('tl_class' === $inputName) {
            $javaScripts[] = 'bundles/metamodelscore/js/stylepicker.js';
        }

        $configuration = $this->configuration[$request->get('item')];
        // Backwards compatibility - configuration was once passed via config.php.
        if (isset($GLOBALS[$request->get('item')])) {
            $configuration = array_merge_recursive($configuration, $GLOBALS[$request->get('item')]);
        }

        $items  = [];
        $prefix = 'MSC.' . $inputName . '.';
        foreach ($configuration as $item) {
            $label = $this->translator->trans($prefix . $item['cssclass'], [], 'contao_default');
            $descr = '';
            if (\is_array($label)) {
                list($label, $descr) = $label;
            }

            $items[] = [
                'label'       => $label,
                'description' => $descr,
                'cssclass'    => $item['cssclass'],
            ];
        }

        return new Response(
            $this->templating->render(
                'MetaModelsCoreBundle:Backend:be_dcastylepicker.html.twig',
                [
                    'items'       => $items,
                    'field'       => $request->get('inputName'),
                    'stylesheets' => $styleSheets,
                    'javascripts' => $javaScripts,
                    'headline'    => $this->translator->trans('MSC.metamodelspicker', [], 'contao_default'),
                    'noItems'     => $this->translator->trans('MSC.metamodelspicker_noItems', [], 'contao_default'),
                ]
            )
        );
    }
}
