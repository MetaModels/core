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
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Widgets;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use RuntimeException;

/**
 * This class handles the DCA style picker.
 */
class PickerWidget
{
    /**
     * Initialize the controller.
     *
     * The workflow is:
     * 1. Import user.
     * 2. Call parent constructor
     * 3. Authenticate user
     * 4. Load language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        BackendUser::getInstance()->authenticate();
        Controller::loadLanguageFile('default');
        Controller::loadLanguageFile('modules');
    }

    /**
     * Run controller and parse the login template.
     *
     * @return void
     *
     * @throws RuntimeException When the fieldname is invalid.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function run()
    {
        $template           = new BackendTemplate('be_dcastylepicker');
        $template->main     = '';
        $template->headline = $GLOBALS['TL_LANG']['MSC']['metamodelspicker'];

        $inputName = Input::get('inputName');
        if (!preg_match('~^[a-z\-_0-9]+$~i', $inputName)) {
            throw new RuntimeException('Field-Parameter ERROR!');
        }

        $template->field = $inputName;
        $template->items = $GLOBALS[Input::get('item')];

        if (!strlen($template->headline)) {
            $template->headline = $GLOBALS['TL_CONFIG']['websiteTitle'];
        }

        $template->theme          = Backend::getTheme();
        $template->base           = Environment::get('base');
        $template->language       = $GLOBALS['TL_LANGUAGE'];
        $template->title          = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $template->charset        = $GLOBALS['TL_CONFIG']['characterSet'];
        $template->pageOffset     = Input::cookie('BE_PAGE_OFFSET');
        $template->error          = (Input::get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
        $template->skipNavigation = $GLOBALS['TL_LANG']['MSC']['skipNavigation'];
        $template->request        = ampersand(Environment::get('request'));
        $template->top            = $GLOBALS['TL_LANG']['MSC']['backToTop'];
        $template->be27           = !$GLOBALS['TL_CONFIG']['oldBeTheme'];
        $template->expandNode     = $GLOBALS['TL_LANG']['MSC']['expandNode'];
        $template->collapseNode   = $GLOBALS['TL_LANG']['MSC']['collapseNode'];
        $template->strField       = Input::get('fld');

        $template->output();
    }
}
