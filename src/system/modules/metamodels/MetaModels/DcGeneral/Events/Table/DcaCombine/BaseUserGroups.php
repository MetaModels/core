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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\DcaCombine;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Provide base method for retrieving user groups from a table.
 *
 * @package MetaModels\DcGeneral\Events\Table\DcaCombine
 */
class BaseUserGroups
{
	/**
	 * Get all options for the frontend user groups.
	 *
	 * @param string                  $table The source table.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException When an invalid table name.
	 */
	public static function getUserGroups($table, GetPropertyOptionsEvent $event)
	{
		if (!in_array($table, array('tl_user_group', 'tl_member_group')))
		{
			throw new \RuntimeException('Unexpected table name ' . $table, 1);
		}
		$groups = \Database::getInstance()->execute(sprintf('SELECT id,name FROM %s', $table));

		$result = array();
		if ($table == 'tl_user_group')
		{
			$result[-1] = $event->getEnvironment()->getTranslator()->translate('sysadmin', 'tl_metamodel_dca_combine');
		}

		while ($groups->next())
		{
			$result[$groups->id] = $groups->name;
		}

		$event->setOptions($result);
	}
}
