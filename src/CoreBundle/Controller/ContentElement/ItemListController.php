<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use MetaModels\CoreBundle\Controller\ListControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The item list content element.
 *
 * @ContentElement("metamodel_content", category="metamodels", template="ce_metamodel_content")
 */
final class ItemListController extends AbstractContentElementController
{
    use ListControllerTrait;

    /**
     * Override the template and return the response.
     *
     * @param Request      $request The request.
     * @param ContentModel $model   The content model.
     * @param string       $section The layout section, e.g. "main".
     * @param array|null   $classes The css classes.
     *
     * @return Response The response.
     */
    public function __invoke(Request $request, ContentModel $model, string $section, array $classes = null): Response
    {
        if ($this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $this->getBackendWildcard($model);
        }

        if (!empty($model->metamodel_layout)) {
            $model->customTpl = $model->metamodel_layout;
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Generate the response.
     *
     * @param Template     $template The template.
     * @param ContentModel $model    The content model.
     * @param Request      $request  The request.
     *
     * @return Response The response.
     */
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $response = $this->getResponseInternal($template, $model, $request);
        $this->addSharedMaxAgeToResponse($response, $model);

        return $response;
    }

    /**
     * Return a back end wildcard response.
     *
     * @return Response The repsonse.
     */
    private function getBackendWildcard(): Response
    {
        $name = $this->get('translator')->trans('CTE.' . $this->getType() . '.0', [], 'contao_modules');

        $template = new BackendTemplate('be_wildcard');

        $template->wildcard = '### ' . strtoupper($name) . ' ###';

        return new Response($template->parse());
    }
}
