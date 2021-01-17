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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use MetaModels\CoreBundle\Controller\ListControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The item list front end module.
 *
 * @FrontendModule("metamodel_list", category="metamodels")
 */
final class ItemListController extends AbstractFrontendModuleController
{
    use ListControllerTrait;

    /**
     * Override the template and return the response.
     *
     * @param Request        $request   The request.
     * @param ModuleModel    $model     The module model.
     * @param string         $section   The layout section, e.g. "main".
     * @param array|null     $classes   The css classes.
     * @param PageModel|null $pageModel The page model.
     *
     * @return Response The response.
     */
    public function __invoke(
        Request $request,
        ModuleModel $model,
        string $section,
        array $classes = null,
        PageModel $pageModel = null
    ): Response {
        if (!empty($model->metamodel_layout)) {
            $model->customTpl = $model->metamodel_layout;
        }

        return parent::__invoke($request, $model, $section, $classes, $pageModel);
    }

    /**
     * Return a back end wildcard response.
     *
     * @param ModuleModel $module The module model.
     *
     * @return Response The response.
     */
    protected function getBackendWildcard(ModuleModel $module): Response
    {
        $name = $this->get('translator')->trans('FMD.'.$this->getType().'.0', [], 'contao_modules');
        $href = $this->get('router')->generate(
            'contao_backend',
            ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $module->id]
        );

        return $this->renderBackendWildcard($href, $name, $module);
    }

    /**
     * Generate the response.
     *
     * @param Template    $template The template.
     * @param ModuleModel $model    The module model.
     * @param Request     $request  The request.
     *
     * @return Response The response.
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        return $this->getResponseInternal($template, $model, $request);
    }
}
