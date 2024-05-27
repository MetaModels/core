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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

/**
 * This controller provides the add-all action in input screens.
 */
class SupportMetaModelsController extends AbstractBackendController
{
    /**
     * The twig engine.
     *
     * @var TwigEnvironment
     */
    private TwigEnvironment $twig;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The github contributors file.
     *
     * @var string
     */
    private string $github;

    /**
     * The transifex contributors file.
     *
     * @var string
     */
    private string $transifex;

    /**
     * Create a new instance.
     *
     * @param TwigEnvironment     $twig       The twig engine.
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
        $headline = $this->translator->trans('menu.label', [], 'metamodels_support');

        $GLOBALS['TL_CSS']['metamodels.core'] = '/bundles/metamodelscore/css/supportscreen.css';

        return $this->render(
            '@MetaModelsCore/misc/support.html.twig',
            [
                'title'                  => $headline,
                'headline'               => $headline,
                'sub_headline'           =>
                    $this->translator->trans('main_headline', [], 'metamodels_support'),
                'head_contributor'       =>
                    $this->translator->trans('contributor_headline', [], 'metamodels_support'),
                'purpose'                => $this->translator->trans('purpose', [], 'metamodels_support'),
                'other_donations'        =>
                    $this->translator->trans('other_donations', [], 'metamodels_support'),
                'main_text'              =>
                    $this->translator->trans('main_text', [], 'metamodels_support'),
                'help_headline'          =>
                    $this->translator->trans('help_headline', [], 'metamodels_support'),
                'help_text'              =>
                    $this->translator->trans('help_text', [], 'metamodels_support'),
                'github_contributors'    => $this->getJsonFile($this->github),
                'transifex_contributors' => $this->getJsonFile($this->transifex)
            ]
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
        if (!\is_readable($filename)) {
            return [];
        }

        $contents = \json_decode(\file_get_contents($filename), true);

        return $contents ?: [];
    }
}
