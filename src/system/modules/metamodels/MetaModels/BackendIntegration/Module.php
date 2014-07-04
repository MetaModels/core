<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Event\EventPropagator;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implementation of the MetaModel Backend Module that performs system checks
 * before allowing access to MetaModel configuration etc. Everything below
 * http://..../contao/main.php?do=metamodels&.... ends up here.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Module
{
	/**
	 * The template to use.
	 *
	 * @var string
	 */
	protected $strTemplate = 'be_detectedproblems';

	/**
	 * The current BackendTemplate instance.
	 *
	 * @var \BackendTemplate
	 */
	protected $Template;

	/**
	 * The message log.
	 *
	 * @var array
	 */
	protected static $arrMessages = array();

	/**
	 * Buffer a message in the stack.
	 *
	 * @param string $strOutput      The message to be displayed (HTML welcome).
	 *
	 * @param int    $intSeverity    May be METAMODELS_INFO, METAMODELS_WARN, METAMODELS_ERROR.
	 *
	 * @param string $strHelpfulLink The (backend)-link to some location to resolve the problem.
	 *
	 * @return void
	 */
	public static function addMessageEntry($strOutput, $intSeverity = METAMODELS_INFO, $strHelpfulLink = '')
	{
		self::$arrMessages[$intSeverity][] = array('message' => $strOutput, 'link' => $strHelpfulLink);
	}

	/**
	 * Add a suffix to the current url.
	 *
	 * @param string $suffix The suffix to add.
	 *
	 * @return string
	 */
	public function addToUrl($suffix)
	{
		/** @var EventDispatcherInterface $dispatcher */
		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$event      = new AddToUrlEvent($suffix);

		$dispatcher->dispatch($event);

		return $event->getUrl();
	}

	/**
	 * Ensure we have at least PHP 5.3.
	 *
	 * @return void
	 */
	protected function checkPHPVersion()
	{
		if (version_compare(PHP_VERSION, '5.3') < 0)
		{
			$this->addMessageEntry(
				sprintf($GLOBALS['TL_LANG']['ERR']['upgrade_php_version'], '5.3', PHP_VERSION),
				METAMODELS_ERROR,
				'http://www.php.org/'
			);
		}
	}

	/**
	 * Check if all dependencies are present.
	 *
	 * @return void
	 */
	protected function checkDependencies()
	{
		$arrActiveModules   = \Config::getInstance()->getActiveModules();
		$arrInactiveModules = deserialize($GLOBALS['TL_CONFIG']['inactiveModules']);

		// Check if all prerequsities are met.
		foreach ($GLOBALS['METAMODELS']['dependencies'] as $strExtension => $strDisplay)
		{
			if (!in_array($strExtension, $arrActiveModules))
			{
				if (is_array($arrInactiveModules) && in_array($strExtension, $arrInactiveModules))
				{
					$this->addMessageEntry(
						sprintf($GLOBALS['TL_LANG']['ERR']['activate_extension'], $strDisplay, $strExtension),
						METAMODELS_ERROR,
						$this->addToUrl('do=settings')
					);
				} else {
					$this->addMessageEntry(
						sprintf($GLOBALS['TL_LANG']['ERR']['install_extension'], $strDisplay, $strExtension),
						METAMODELS_ERROR,
						$this->addToUrl('do=repository_catalog&view=' . $strDisplay)
					);
				}
			}
		}
	}

	/**
	 * Check if at least one attribute extension is installed and activated, if not display link to ER catalog.
	 *
	 * @return void
	 */
	protected function hasAttributes()
	{
		if (!$GLOBALS['METAMODELS']['attributes'])
		{
			$this->addMessageEntry(
				$GLOBALS['TL_LANG']['ERR']['no_attribute_extension'],
				METAMODELS_INFO,
				$this->addToUrl('do=repository_catalog')
			);
		}
	}

	/**
	 * Check if user action is needed.
	 *
	 * @return bool
	 */
	protected function needUserAction()
	{
		// Run the embedded methods now.
		$this->checkPHPVersion();
		$this->checkDependencies();
		$this->hasAttributes();

		if ($GLOBALS['METAMODELS']['CHECK'])
		{
			// Loop through all metamodel backend checkers.
			foreach ($GLOBALS['METAMODELS']['CHECK'] as $strClass)
			{
				Callbacks::call(array($strClass, 'perform'), $this);
			}
		}
		return count(self::$arrMessages) > 0;
	}

	/**
	 * Run the data container.
	 *
	 * @return string
	 */
	protected function runDC()
	{
		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher    = $GLOBALS['container']['event-dispatcher'];
		$propagator    = new EventPropagator($dispatcher);
		$translator    = new TranslatorChain();
		$factory       = new DcGeneralFactory();
		$backendModule = \Input::getInstance()->get('do');

		$translator->add(new LangArrayTranslator($dispatcher));

		$factory
			->setEventPropagator($propagator)
			->setTranslator($translator);

		if ($backendModule == 'metamodels')
		{
			$name = \Input::getInstance()->get('table');
			if (!$name)
			{
				$name = 'tl_metamodel';
			}
		}
		elseif(\Input::getInstance()->get('table'))
		{
			$name = \Input::getInstance()->get('table');
		}
		else
		{
			$name = substr($backendModule, 10);
		}

		$dcg = $factory
			->setContainerName($name)
			->createDcGeneral();

		$act = \Input::getInstance()->get('act');
		if (!strlen($act))
		{
			$act = 'showAll';
		}

		return $dcg->getEnvironment()->getController()->handle(new Action($act));
	}

	/**
	 * Handler object for key operation.
	 *
	 * @var object
	 */
	protected $objKeyHandler = null;

	/**
	 * Perform the normal operation, no user action is required.
	 *
	 * @return string
	 */
	protected function performNormal()
	{
		$arrModule = $GLOBALS['BE_MOD']['metamodels']['metamodels'];
		// Custom action (if key is not defined in config.php the default action will be called).
		if (\Input::getInstance()->get('key') && isset($arrModule[\Input::getInstance()->get('key')]))
		{
			Callbacks::call($arrModule[\Input::getInstance()->get('key')], $this, $arrModule);
		}
		return $this->runDC();
	}

	/**
	 * Parse the template.
	 *
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_CSS'][] = 'system/modules/metamodels/assets/css/style.css';
		if ($this->needUserAction())
		{
			$this->Template = new \BackendTemplate($this->strTemplate);
			$this->compile();

			return $this->Template->parse();
		}
		return $this->performNormal();
	}

	/**
	 * Compile the current element.
	 *
	 * @return void
	 */
	protected function compile()
	{
		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$event      = new GetReferrerEvent(true);

		$dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $event);

		$this->Template->href     = $event->getReferrerUrl();
		$this->Template->problems = self::$arrMessages;
	}
}
