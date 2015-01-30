<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Widgets;

/**
 * This class handles the DCA style picker.
 */
class PickerWidget extends \Backend
{
    /**
     * Current Ajax object.
     *
     * @var object
     */
    protected $objAjax;

    /**
     * The backend user.
     *
     * @var \Contao\BackendUser
     */
    private $User;

    /**
     * The template instance.
     *
     * @var \Contao\BackendTemplate
     */
    private $Template;

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
        $this->User = \BackendUser::getInstance();
        parent::__construct();

        $this->User->authenticate();

        $this->loadLanguageFile('default');
        $this->loadLanguageFile('modules');
    }

    /**
     * Generate the template.
     *
     * @return void
     *
     * @throws \RuntimeException When the fieldname is invalid.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generate()
    {
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['metamodelspicker'];

        $inputName = $this->Input->get('inputName');
        if (!preg_match('~^[a-z\-_0-9]+$~i', $inputName)) {
            throw new \RuntimeException('Field-Parameter ERROR!');
        }

        $this->Template->field = $inputName;
        $this->Template->items = $GLOBALS[$this->Input->get('item')];
    }


    /**
     * Run controller and parse the login template.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function run()
    {
        $this->Template       = new \BackendTemplate('be_dcastylepicker');
        $this->Template->main = '';
        $this->generate();

        if (!strlen($this->Template->headline)) {
            $this->Template->headline = $GLOBALS['TL_CONFIG']['websiteTitle'];
        }

        $this->Template->theme          = $this->getTheme();
        $this->Template->base           = $this->Environment->base;
        $this->Template->language       = $GLOBALS['TL_LANGUAGE'];
        $this->Template->title          = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $this->Template->charset        = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->Template->pageOffset     = $this->Input->cookie('BE_PAGE_OFFSET');
        $this->Template->error          = (\Input::get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
        $this->Template->skipNavigation = $GLOBALS['TL_LANG']['MSC']['skipNavigation'];
        $this->Template->request        = ampersand($this->Environment->request);
        $this->Template->top            = $GLOBALS['TL_LANG']['MSC']['backToTop'];
        $this->Template->be27           = !$GLOBALS['TL_CONFIG']['oldBeTheme'];
        $this->Template->expandNode     = $GLOBALS['TL_LANG']['MSC']['expandNode'];
        $this->Template->collapseNode   = $GLOBALS['TL_LANG']['MSC']['collapseNode'];
        $this->Template->strField       = $this->Input->get('fld');

        $this->Template->output();
    }
}
