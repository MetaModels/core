<?php
/**
 *
 * PHP version 5
 * @copyright  The MetaModels team.
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @package    stylepicker4ward
 * @filesource
 */

define('TL_MODE', 'BE');

// Search the initialize.php.
$dir = dirname($_SERVER['SCRIPT_FILENAME']);

while ($dir != '.' && $dir != '/' && !is_file($dir . '/system/initialize.php'))
{
	$dir = dirname($dir);
}

if (!is_file($dir . '/system/initialize.php'))
{
	echo 'Could not find initialize.php, where is Contao?';
	exit;
}

/**
 * This class handles the DCA style picker.
 */
// @codingStandardsIgnoreStart - Ignore that the class name is not in camel case.
class popup extends Backend
// @codingStandardsIgnoreEnd
{

	/**
	 * Current Ajax object.
	 *
	 * @var object
	 */
	protected $objAjax;


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
		$this->import('BackendUser', 'User');
		$this->import('Database');
		parent::__construct();

		$this->User->authenticate();

		$this->loadLanguageFile('default');
		$this->loadLanguageFile('modules');
	}

	/**
	 * Generate the template.
	 *
	 * @return void
	 */
	public function generate()
	{
		$this->Template->headline = $GLOBALS['TL_LANG']['MSC']['stylepicker4ward'];

		$inputName = $this->Input->get('inputName');
		if (!preg_match('~^[a-z\-_0-9]+$~i', $inputName))
		{
			die('Field-Parameter ERROR!');
		}
		$this->Template->field = $inputName;
		$this->Template->items = $GLOBALS[$this->Input->get('item')];
	}


	/**
	 * Run controller and parse the login template.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->Template       = new BackendTemplate('be_dcastylepicker');
		$this->Template->main = '';
		$this->generate();

		if (!strlen($this->Template->headline))
		{
			$this->Template->headline = $GLOBALS['TL_CONFIG']['websiteTitle'];
		}

		$this->Template->theme          = $this->getTheme();
		$this->Template->base           = $this->Environment->base;
		$this->Template->language       = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title          = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$this->Template->charset        = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->pageOffset     = $this->Input->cookie('BE_PAGE_OFFSET');
		$this->Template->error          = ($this->Input->get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
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

// Run the style picker.
$x = new popup();
$x->run();
