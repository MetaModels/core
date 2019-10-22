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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Environment as TwigEnvironment;

/**
 * This controller provides the add-all action in input screens.
 */
class SupportMetaModelsController
{
    /**
     * The twig engine.
     *
     * @var TwigEnvironment
     */
    private $twig;

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
     * @param Environment         $twig       The twig engine.
     * @param TranslatorInterface $translator The translator.
     * @param string              $github     Path to the github contributor json list.
     * @param string              $transifex  Path to the transifex contributor json list.
     */
    public function __construct(TwigEnvironment $twig, TranslatorInterface $translator, $github, $transifex)
    {
        $this->twig       = $twig;
        $this->translator = $translator;
        $this->github     = $github;
        $this->transifex  = $transifex;
    }

    /**
     * Invoke this.
     *
     * @return Response The template data.
     */
    public function __invoke()
    {
        return new Response(
            $this->twig->render(
                '@MetaModelsCore/misc/support.html.twig',
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
