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
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This controller provides the add-all action in input screens.
 */
class SupportMetaModelsController
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
     * The github contributors file.
     *
     * @var string
     */
    private $github;

    /**
     * The transifex contributors file.
     *
     * @var string
     */
    private $transifex;

    /**
     * Create a new instance.
     *
     * @param EngineInterface     $templating The twig engine.
     * @param TranslatorInterface $translator The translator.
     * @param string              $github     Path to the github contributor json list.
     * @param string              $transifex  Path to the transifex contributor json list.
     */
    public function __construct(EngineInterface $templating, TranslatorInterface $translator, $github, $transifex) {
        $this->templating = $templating;
        $this->translator = $translator;
        $this->github     = $github;
        $this->transifex  = $transifex;
    }

    /**
     * @return Response The template data.
     */
    public function __invoke()
    {
        // FIXME: can be removed when https://github.com/contao/core-bundle/pull/1153 is merged.
        $GLOBALS['TL_CSS'][] = 'bundles/metamodelscore/css/supportscreen.css';
        return new Response(
            $this->templating->render(
                'MetaModelsCoreBundle::misc/support.html.twig',
                [
                    'stylesheets' => [
                        'bundles/metamodelscore/css/supportscreen.css'
                    ],
                    'headline' => $this->translator->trans('MOD.support_metamodels.0', [], 'contao_modules'),
                    'sub_headline' =>
                        $this->translator->trans('MSC.metamodels_support.main_headline', [], 'contao_default'),
                    'head_contributor' =>
                        $this->translator->trans('MSC.metamodels_support.contributor_headline', [], 'contao_default'),
                    'purpose' => $this->translator->trans('MSC.metamodels_support.purpose', [], 'contao_default'),
                    'other_donations' =>
                        $this->translator->trans('MSC.metamodels_support.other_donations', [], 'contao_default'),
                    'main_text' =>
                        $this->translator->trans('MSC.metamodels_support.main_text', [], 'contao_default'),
                    'help_headline' =>
                        $this->translator->trans('MSC.metamodels_support.help_headline', [], 'contao_default'),
                    'help_text' =>
                        $this->translator->trans('MSC.metamodels_support.help_text', [], 'contao_default'),
                    'github_contributors' => $this->getJsonFile($this->github),
                    'transifex_contributors' => $this->getJsonFile($this->transifex)
                ]
            )
        );
    }

    /**
     * Load the passed file and decode it.
     *
     * @param string $filename The file name.
     *
     * @return array
     */
    private function getJsonFile($filename)
    {
        if (!is_readable($filename)) {
            return [];
        }

        $contents = json_decode(file_get_contents($filename), true);

        return $contents ?: [];
    }
}
