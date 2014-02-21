<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use DcGeneral\DataContainerInterface;
use MetaModels\Factory;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class is used from DCA tl_metamodel_dcasetting for the "add all" global operation.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class DcaSettingAddAll
{
	/**
	 * Retrieve the symfony event dispatcher.
	 *
	 * @return EventDispatcherInterface
	 */
	protected function getDispatcher()
	{
		return $GLOBALS['container']['event-dispatcher'];
	}

	/**
	 * Load needed language files.
	 *
	 * @return void
	 */
	protected function loadLanguageFiles()
	{
		$this->getDispatcher()->dispatch(
			ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
			new LoadLanguageFileEvent('default')
		);
		$this->getDispatcher()->dispatch(
			ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
			new LoadLanguageFileEvent('tl_metamodel_dcasetting')
		);
	}

	/**
	 * Retrieve the current referrer.
	 *
	 * @return string
	 */
	protected function getReferrer()
	{
		$event = $this->getDispatcher()->dispatch(
			ContaoEvents::SYSTEM_GET_REFERRER,
			new GetReferrerEvent(true)
		);

		/** @var GetReferrerEvent $event */
		return $event->getReferrerUrl();
	}

	/**
	 * Add all missing entries to the database.
	 *
	 * @param IMetaModel $metaModel The MetaModel instance.
	 *
	 * @param array      $known     The known attribute ids.
	 *
	 * @param int        $max       The current max value for sorting.
	 *
	 * @return array
	 */
	protected function run(IMetaModel $metaModel, $known, $max)
	{
		$subPaletteId = \Input::getInstance()->get('subpaletteid');
		$messages     = array();

		// Loop over all attributes now.
		foreach ($metaModel->getAttributes() as $objAttribute)
		{
			if (!array_key_exists($objAttribute->get('id'), $known))
			{
				$max += 128;
				\Database::getInstance()->prepare('INSERT INTO tl_metamodel_dcasetting %s')->set(array(
					'pid'      => \Input::getInstance()->get('id'),
					'sorting'  => $max,
					'tstamp'   => time(),
					'dcatype'  => 'attribute',
					'attr_id'  => $objAttribute->get('id'),
					'tl_class' => '',
					'subpalette' => $subPaletteId ? $subPaletteId : 0,
				))->execute();

				// Get msg for adding at main palette or a subpalette.
				if (\Input::getInstance()->get('subpaletteid'))
				{
					$strParentAttributeName = \Input::getInstance()->get('subpaletteid');

					// Get parent setting.
					$objParentDcaSetting = \Database::getInstance()
						->prepare('SELECT attr_id FROM tl_metamodel_dcasetting WHERE id=?')
						->execute($subPaletteId);

					// Check if we have a attribute.
					$objParentAttribute = $metaModel->getAttributeById($objParentDcaSetting->attr_id);

					if (!is_null($objParentAttribute))
					{
						$strParentAttributeName = $objParentAttribute->getName();
					}

					$messages[sprintf(
						$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess_subpalette'],
						$objAttribute->getName(),
						$strParentAttributeName
					)] = 'confirm';
				}
				else
				{
					$messages[sprintf(
						$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess'],
						$objAttribute->getName()
					)] = 'confirm';
				}
			}
		}

		return $messages;
	}

	/**
	 * Generate screen.
	 *
	 * @return string
	 */
	public function addAll()
	{
		$this->loadLanguageFiles();

		$referrer = $this->getReferrer();

		$this->Template                = new \BackendTemplate('be_autocreatepalette');
		$this->Template->cacheMessage  = '';
		$this->Template->updateMessage = '';
		$this->Template->href          = $referrer;
		$this->Template->headline      = $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1];

		// Severity is one of: error, confirm, info, new.
		$arrMessages = array();

		$objPalette = \Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
			->limit(1)
			->execute(\Input::getInstance()->get('pid'));

		$objMetaModel = Factory::byId($objPalette->pid);

		$objAlreadyExist = \Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND dcatype=?')
			->execute(\Input::getInstance()->get('pid'), 'attribute');

		$arrKnown = array();
		$intMax   = 128;
		while ($objAlreadyExist->next())
		{
			$arrKnown[$objAlreadyExist->attr_id] = $objAlreadyExist->row();
			if ($intMax < $objAlreadyExist->sorting)
			{
				$intMax = $objAlreadyExist->sorting;
			}
		}

		$blnWantPerform = false;
		// Perform the labour work.
		if (\Input::getInstance()->post('act') == 'perform')
		{
			$arrMessages = $this->run($objMetaModel, $arrKnown, $intMax);
		} else {
			// Loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$arrMessages[sprintf(
						$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained'],
						$objAttribute->getName()
					)] = 'info';
				} else {
					$arrMessages[sprintf(
						$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd'],
						$objAttribute->getName()
					)] = 'confirm';

					$blnWantPerform = true;
				}
			}
		}

		if ($blnWantPerform)
		{
			$this->Template->action = ampersand($this->Environment->request);
			$this->Template->submit = $GLOBALS['TL_LANG']['MSC']['continue'];
		} else {
			$this->Template->action = ampersand($referrer);
			$this->Template->submit = $GLOBALS['TL_LANG']['MSC']['saveNclose'];
		}

		$this->Template->error = $arrMessages;

		return $this->Template->parse();
	}
}
