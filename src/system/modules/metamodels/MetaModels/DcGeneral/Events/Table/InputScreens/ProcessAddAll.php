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

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use MetaModels\Factory as ModelFactory;
use MetaModels\IMetaModel;

/**
 * Process the "add all" button.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreens
 */
class ProcessAddAll
{
	/**
	 * Perform the action.
	 *
	 * @param IMetaModel $metaModel       The MetaModel.
	 *
	 * @param array      $knownAttributes The list of known attributes.
	 *
	 * @param int        $startSort       The first sort index.
	 *
	 * @param int        $pid             The pid.
	 *
	 * @param int        $subPalette      The id of the attribute that this attribute depends on.
	 *
	 * @param array      $messages        The messages array.
	 *
	 * @return void
	 */
	protected static function perform(IMetaModel $metaModel, $knownAttributes, $startSort, $pid, $subPalette, &$messages)
	{
		$database = \Database::getInstance();

		// Loop over all attributes now.
		foreach ($metaModel->getAttributes() as $attribute)
		{
			if (!array_key_exists($attribute->get('id'), $knownAttributes))
			{
				$arrData = array
				(
					'pid'      => $pid,
					'sorting'  => $startSort,
					'tstamp'   => time(),
					'dcatype'  => 'attribute',
					'attr_id'  => $attribute->get('id'),
					'tl_class' => '',
					'subpalette' => $subPalette ? $subPalette : 0,
				);

				$startSort += 128;
				$database
					->prepare('INSERT INTO tl_metamodel_dcasetting %s')
					->set($arrData)
					->execute();

				if ($subPalette)
				{
					$parentAttributeName = $subPalette;
					// Get parent setting.
					$parentDcaSetting = \Database::getInstance()
						->prepare('SELECT attr_id FROM tl_metamodel_dcasetting WHERE id=?')
						->execute($subPalette);

					// Check if we have a attribute.
					$parentAttribute = $metaModel->getAttributeById($parentDcaSetting->attr_id);

					if (!is_null($parentAttribute))
					{
						$parentAttributeName = $parentAttribute->getName();
					}

					$messages[] = array
					(
						'severity' => 'confirm',
						'message'  => sprintf(
							$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess_subpalette'],
							$attribute->getName(),
							$parentAttributeName
						),
					);
				}
				else
				{
					$messages[] = array
					(
						'severity' => 'confirm',
						'message'  => sprintf(
							$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess'],
							$attribute->getName()
						),
					);
				}
			}
		}
	}

	/**
	 * Handle the add all action event.
	 *
	 * @param ActionEvent $event The event.
	 *
	 * @return void
	 */
	public static function handleAddAll(ActionEvent $event)
	{
		if ($event->getAction()->getName() !== 'dca_addall')
		{
			return;
		}

		$environment = $event->getEnvironment();
		$propagator  = $environment->getEventPropagator();
		$database    = \Database::getInstance();
		$input       = $environment->getInputProvider();
		$pid         = IdSerializer::fromSerialized($input->getParameter('pid'));

		$event->getAction()->getName();

		$propagator->propagate(
			ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
			new LoadLanguageFileEvent('default')
		);
		$propagator->propagate(
			ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
			new LoadLanguageFileEvent('tl_metamodel_dcasetting')
		);
		$referrer = new GetReferrerEvent(true, 'tl_metamodel_dcasetting');
		$propagator->propagate(
			ContaoEvents::SYSTEM_GET_REFERRER,
			$referrer
		);

		$template = new \BackendTemplate('be_autocreatepalette');

		$template->cacheMessage  = '';
		$template->updateMessage = '';
		$template->href          = $referrer->getReferrerUrl();
		$template->headline      = $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1];

		// Severity is: error, confirm, info, new.
		$messages = array();

		$palette = $database
			->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
			->execute($pid->getId());

		$metaModel = ModelFactory::byId($palette->pid);

		$alreadyExisting = $database
			->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=?')
			->execute($pid->getId());

		$knownAttributes = array();
		$intMax          = 128;
		while ($alreadyExisting->next())
		{
			$knownAttributes[$alreadyExisting->attr_id] = $alreadyExisting->row();
			if ($intMax < $alreadyExisting->sorting)
			{
				$intMax = $alreadyExisting->sorting;
			}
		}

		$blnWantPerform = false;
		// Perform the labour work.
		if ($input->getValue('act') == 'perform')
		{
			self::perform(
				$metaModel,
				$knownAttributes,
				$intMax,
				$pid->getId(),
				$input->getParameter('subpaletteid'),
				$messages
			);
		}
		else
		{
			// Loop over all attributes now.
			foreach ($metaModel->getAttributes() as $attribute)
			{
				if (array_key_exists($attribute->get('id'), $knownAttributes))
				{
					$messages[] = array
					(
						'severity' => 'info',
						'message'  => sprintf(
							$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained'],
							$attribute->getName()
						),
					);
				}
				else
				{
					$messages[] = array
					(
						'severity' => 'confirm',
						'message'  => sprintf(
							$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd'],
							$attribute->getName()
						),
					);

					$blnWantPerform = true;
				}
			}
		}

		if ($blnWantPerform)
		{
			$template->action = ampersand(\Environment::getInstance()->request);
			$template->submit = $GLOBALS['TL_LANG']['MSC']['continue'];
		}
		else
		{
			$template->action = ampersand($referrer->getReferrerUrl());
			$template->submit = $GLOBALS['TL_LANG']['MSC']['saveNclose'];
		}

		$template->error = $messages;

		$event->setResponse($template->parse());
	}
}
