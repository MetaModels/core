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

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Contao\Callback\CallBacks;
use DcGeneral\Contao\LangArrayTranslator;
use DcGeneral\Event\EventPropagator;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use DcGeneral\TranslatorChain;
use MetaModels\DcGeneral\Dca\Builder\Builder;

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
	 * The template to use
	 * @var string
	 */
	protected $strTemplate = 'be_detectedproblems';

	protected $Template;

	protected static $arrMessages = array();

	/**
	 * Buffer a message in the stack.
	 *
	 * @param string $strOutput      the message to be displayed (HTML welcome).
	 *
	 * @param int    $intSeverity    may be METAMODELS_INFO, METAMODELS_WARN, METAMODELS_ERROR
	 *
	 * @param string $strHelpfulLink (backend)-link to some location to resolve the problem.
	 */
	public static function addMessageEntry($strOutput, $intSeverity = METAMODELS_INFO, $strHelpfulLink = '')
	{
		self::$arrMessages[$intSeverity][] = array('message' => $strOutput, 'link' => $strHelpfulLink);
	}

	/**
	 * Ensure we have at least PHP 5.3
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
	 */
	protected function checkDependencies()
	{
		$arrActiveModules = \Config::getInstance()->getActiveModules();
		$arrInactiveModules = deserialize($GLOBALS['TL_CONFIG']['inactiveModules']);

		// check if all prerequsities are met.
		foreach($GLOBALS['METAMODELS']['dependencies'] as $strExtension => $strDisplay)
		{
			if (!in_array($strExtension, $arrActiveModules))
			{
				if (is_array($arrInactiveModules) && in_array($strExtension, $arrInactiveModules))
				{
					$this->addMessageEntry(
						sprintf($GLOBALS['TL_LANG']['ERR']['activate_extension'], $strDisplay, $strExtension),
						METAMODELS_ERROR,
						BackendBindings::addToUrl('do=settings')
					);
				} else {
					$this->addMessageEntry(
						sprintf($GLOBALS['TL_LANG']['ERR']['install_extension'], $strDisplay, $strExtension),
						METAMODELS_ERROR,
						BackendBindings::addToUrl('do=repository_catalog&view=' . $strDisplay)
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
				BackendBindings::addToUrl('do=repository_catalog')
			);
		}
	}

	protected function needUserAction()
	{
		// run the embedded methods now:
		$this->checkPHPVersion();
		$this->checkDependencies();
		$this->hasAttributes();

		if ($GLOBALS['METAMODELS']['CHECK'])
		{
			// loop through all metamodel backend checkers.
			foreach ($GLOBALS['METAMODELS']['CHECK'] as $strClass)
			{
				CallBacks::call(array($strClass, 'perform'), $this);
			}
		}
		return count(self::$arrMessages)> 0;
	}

	protected function runDC()
	{
		global $container;
		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher    = $container['event-dispatcher'];
		$propagator    = new EventPropagator($dispatcher);
		$translator    = new TranslatorChain();
		$factory       = new DcGeneralFactory();
		$backendModule = \Input::getInstance()->get('do');

		$translator->add(new LangArrayTranslator());

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
		else
		{
			$name = substr($backendModule, 10);

			$generator = new Builder();

			$dispatcher->addListener(
				sprintf('%s[%s]', BuildDataDefinitionEvent::NAME, $name),
				array($generator, 'build'),
				$generator::PRIORITY
			);
			$dispatcher->addListener(
				sprintf('%s[%s]', PopulateEnvironmentEvent::NAME, $name),
				array($generator, 'populate'),
				$generator::PRIORITY
			);

			$factory->setContainerClassName('MetaModels\DcGeneral\DataDefinition\MetaModelDataDefinition');
		}

		$dcg = $factory
			->setContainerName($name)
			->createDcGeneral();

		$act = \Input::getInstance()->get('act');
		if (!strlen($act))
		{
			$act = 'showAll';
		}

		return call_user_func(array($dcg->getEnvironment()->getView(), $act));
	}

	/**
	 * handler object for key operation.
	 *
	 * @var object
	 */
	protected $objKeyHandler = null;

	protected function performNormal()
	{
		$arrModule = $GLOBALS['BE_MOD']['metamodels']['metamodels'];
		// Custom action (if key is not defined in config.php the default action will be called)
		if (\Input::getInstance()->get('key') && isset($arrModule[\Input::getInstance()->get('key')]))
		{
			CallBacks::call($arrModule[\Input::getInstance()->get('key')], $this, $arrModule);
		}
		return $this->runDC();
	}

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_CSS'][] = 'system/modules/metamodels/html/style.css';
		if ($this->needUserAction())
		{
			// FIXME: this is broken now.
			return parent::generate();
		} else {
			return $this->performNormal();
		}
	}

	/**
	 * Compile the current element
	 */
	protected function compile()
	{
		$this->Template->href = BackendBindings::getReferer(true);
		$this->Template->problems = self::$arrMessages;
	}
}

