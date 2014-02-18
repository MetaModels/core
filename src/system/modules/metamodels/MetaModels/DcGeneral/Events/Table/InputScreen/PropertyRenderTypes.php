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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * Manipulate the data definition for the property "rendertypes" in table "tl_dca".
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */
class PropertyRenderTypes
{

	/**
	 * Populates an array with all valid "rendertypes".
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getRenderTypes(GetPropertyOptionsEvent $event)
	{
		$event->setOptions(array('standalone', 'ctable'));
	}
}
