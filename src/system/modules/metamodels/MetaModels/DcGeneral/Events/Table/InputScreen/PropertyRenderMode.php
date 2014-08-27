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

namespace MetaModels\DcGeneral\Events\Table\InputScreen;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Manipulate the data definition for the property "rendermode" in table "tl_metamodel_dca".
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */

class PropertyRenderMode
{
	/**
	 * Retrieve a list of all render modes.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getModes(GetPropertyOptionsEvent $event)
	{
		$translator = $event->getEnvironment()->getTranslator();
		$options    = array(
			'flat'         => $translator->translate('rendermodes.flat', 'tl_metamodel_dca'),
			'hierarchical' => $translator->translate('rendermodes.hierarchical', 'tl_metamodel_dca'),
		);

		if ($event->getModel()->getProperty('rendertype') == 'ctable')
		{
			$options['parented'] = $translator->translate('rendermodes.parented', 'tl_metamodel_dca');
		}

		$event->setOptions($options);
	}
}
