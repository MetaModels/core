<?php



class MetaModelBackendModule extends BackendModule
{

	/**
	 * The template to use
	 * @var string
	 */
	protected $strTemplate = 'be_detectedproblems';


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
	 * Check if all dependencies are present.
	 */
	protected function checkDependencies()
	{
		$arrActiveModules = $this->Config->getActiveModules();
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

	protected function needUserAction()
	{
		// run the embedded methods now:
		$this->checkDependencies();
		$this->hasAttributes();

		if ($GLOBALS['METAMODELS']['CHECK'])
		{
			// loop through all metamodel backend checkers.
			foreach ($GLOBALS['METAMODELS']['CHECK'] as $strClass)
			{
				//
				$this->import($strClass);
				$this->$strClass->perform($this->objDc, $this);
			}
		}
		return count(self::$arrMessages)> 0;
	}

	protected function runDC()
	{
		$act = $this->Input->get('act');

		if (!strlen($act) || $act == 'paste' || $act == 'select')
		{
			$act = ($this->objDc instanceof listable) ? 'showAll' : 'edit';
		}

		switch ($act)
		{
			case 'delete':
			case 'show':
			case 'showAll':
			case 'undo':
				if (!$this->objDc instanceof listable)
				{
					$this->log('Data container ' . $this->objDc->table . ' is not listable', 'Backend getBackendModule()', TL_ERROR);
					trigger_error('The current data container is not listable', E_USER_ERROR);
				}
				break;

			case 'create':
			case 'cut':
			case 'cutAll':
			case 'copy':
			case 'copyAll':
			case 'move':
			case 'edit':
				if (!$this->objDc instanceof editable)
				{
					$this->log('Data container ' . $this->objDc->table . ' is not editable', 'Backend getBackendModule()', TL_ERROR);
					trigger_error('The current data container is not editable', E_USER_ERROR);
				}
				break;
		}

		return $this->objDc->$act();
	}

	/**
	 * handler object for key operation.
	 *
	 * @var object
	 */
	protected $objKeyHandler = null;

	protected function performNormal()
	{
		$arrModule = $GLOBALS['BE_MOD']['system']['metamodels'];
		// Custom action (if key is not defined in config.php the default action will be called)
		if ($this->Input->get('key') && isset($arrModule[$this->Input->get('key')]))
		{
			$this->import($arrModule[$this->Input->get('key')][0], 'objKeyHandler');
			return $this->objKeyHandler->$arrModule[$this->Input->get('key')][1]($this->objDc, $this->objDc->table, $arrModule);
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
		$this->Template->href = $this->getReferer(true);
		$this->Template->problems = self::$arrMessages;
	}
}

?>