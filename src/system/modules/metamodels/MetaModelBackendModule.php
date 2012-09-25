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
		$arrMissing = array();

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
						sprintf('Please activate required extension &quot;%s&quot; (%s)', $strDisplay, $strExtension),
						METAMODELS_ERROR,
						$this->addToUrl('do=settings')
					);
				} else {
					$this->addMessageEntry(
						sprintf('Please install required extension &quot;%s&quot; (%s)', $strDisplay, $strExtension),
						METAMODELS_ERROR,
						$this->addToUrl('do=repository_catalog&view=' . $strDisplay)
					);
				}
			}
		}
	}

	protected function hasAttributes()
	{
		if (!$GLOBALS['METAMODELS']['attributes'])
		{
			$this->addMessageEntry(
				'Please install at least one attribute extension as MetaModels without attributes do not make sense.',
				METAMODELS_INFO,
				$this->addToUrl('do=repository_catalog' . $strDisplay)
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
					$this->log('Data container ' . $strTable . ' is not listable', 'Backend getBackendModule()', TL_ERROR);
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
					$this->log('Data container ' . $strTable . ' is not editable', 'Backend getBackendModule()', TL_ERROR);
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
			return $this->objKeyHandler->$arrModule[$this->Input->get('key')][1]($objDc, $objDc->table, $arrModule);
		}
		return $this->runDC();
	}

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
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