<?php
/**
 *
 * PHP version 5
 * @copyright  4ward.media 2011
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @package    stylepicker4ward
 * @filesource
 */



/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
//require_once('../../initialize.php');
require_once('/var/www/entwicklung/_davidmaack/2.11.6/system/initialize.php');

class DCAStylepicker_Wizard extends Backend
{

	/**
	 * Current Ajax object
	 * @var object
	 */
	protected $objAjax;


	/**
	 * Initialize the controller
	 *
	 * 1. Import user
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


	public function generate()
	{
		$this->Template->headline = $GLOBALS['TL_LANG']['MSC']['stylepicker4ward'];
		$inputName = $this->Input->get('inputName');
		if(!preg_match("~^[a-z\-_0-9]+$~i",$inputName))
		{
			die('Field-Parameter ERROR!');
		}
		$this->Template->field = $inputName;
		$this->Template->items = $GLOBALS[$this->Input->get('item')];
	}


	/**
	 * Run controller and parse the login template
	 */
	public function run()
	{
		$this->Template = new BackendTemplate('be_dcastylepicker');
		$this->Template->main = '';

 		$this->Template->main .= $this->generate();

		if (!strlen($this->Template->headline))
		{
			$this->Template->headline = $GLOBALS['TL_CONFIG']['websiteTitle'];
		}

		$this->Template->theme = $this->getTheme();
		$this->Template->base = $this->Environment->base;
		$this->Template->language = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->pageOffset = $this->Input->cookie('BE_PAGE_OFFSET');
		$this->Template->error = ($this->Input->get('act') == 'error') ? $GLOBALS['TL_LANG']['ERR']['general'] : '';
		$this->Template->skipNavigation = $GLOBALS['TL_LANG']['MSC']['skipNavigation'];
		$this->Template->request = ampersand($this->Environment->request);
		$this->Template->top = $GLOBALS['TL_LANG']['MSC']['backToTop'];
		$this->Template->be27 = !$GLOBALS['TL_CONFIG']['oldBeTheme'];
		$this->Template->expandNode = $GLOBALS['TL_LANG']['MSC']['expandNode'];
		$this->Template->collapseNode = $GLOBALS['TL_LANG']['MSC']['collapseNode'];

		$this->Template->output();
	}
}

// run the stuff
$x = new DCAStylepicker_Wizard();
$x->run();

?>