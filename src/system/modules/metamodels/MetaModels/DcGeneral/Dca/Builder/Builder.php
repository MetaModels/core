<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Dca\Builder;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\DcGeneral\DataDefinition\Definition\MetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\Events\MetaModel\RenderItem;
use MetaModels\Factory;
use MetaModels\Helper\ToolboxFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Build the container config from MetaModels information.
 */
class Builder
{
	const PRIORITY = 50;

	/**
	 * The translator instance this builder adds values to.
	 *
	 * @var StaticTranslator
	 */
	protected $translator;

	/**
	 * The event dispatcher currently in use.
	 *
	 * @var EventDispatcherInterface
	 */
	protected $dispatcher;

	/**
	 * Create a new instance and instantiate the translator.
	 */
	public function __construct()
	{
		$this->translator = new StaticTranslator();
	}

	/**
	 * Map all translation values from the given array to the given destination domain using the optional given base key.
	 *
	 * @param array  $array   The array holding the translation values.
	 *
	 * @param string $domain  The target domain.
	 *
	 * @param string $baseKey The base key to prepend the values of the array with.
	 *
	 * @return void
	 */
	protected function mapTranslations($array, $domain, $baseKey = '')
	{
		foreach ($array as $key => $value)
		{
			$newKey = ($baseKey ? $baseKey . '.' : '') . $key;
			if (is_array($value))
			{
				$this->mapTranslations($value, $domain, $newKey);
			}
			else
			{
				$this->translator->setValue($newKey, $value, $domain);
			}
		}
	}

	/**
	 * Handle a populate environment event for MetaModels.
	 *
	 * @param PopulateEnvironmentEvent $event The event.
	 *
	 * @return void
	 */
	public function populate(PopulateEnvironmentEvent $event)
	{
		$container = $event->getEnvironment()->getDataDefinition();

		if (!($container instanceof IMetaModelDataDefinition))
		{
			return;
		}

		$this->dispatcher = $event->getDispatcher();

		$translator = $event->getEnvironment()->getTranslator();

		if (!$translator instanceof TranslatorChain)
		{
			$translatorChain = new TranslatorChain();
			$translatorChain->add($translator);
			$event->getEnvironment()->setTranslator($translatorChain);
		}
		else
		{
			$translatorChain = $translator;
		}

		// Map the tl_metamodel_item domain over to this domain.
		$this->dispatcher->dispatch(
			ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
			new LoadLanguageFileEvent('tl_metamodel_item')
		);

		$this->mapTranslations(
			$GLOBALS['TL_LANG']['tl_metamodel_item'],
			$event->getEnvironment()->getDataDefinition()->getName()
		);

		$translatorChain->add($this->translator);
	}

	/**
	 * Return the input screen details.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return IInputScreen
	 */
	protected function getInputScreenDetails(IMetaModelDataDefinition $container)
	{
		return ViewCombinations::getInputScreenDetails($container->getName());
	}

	/**
	 * Retrieve the MetaModel for the data container.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return \MetaModels\IMetaModel|null
	 */
	protected function getMetaModel(IMetaModelDataDefinition $container)
	{
		return Factory::byTableName($container->getName());
	}

	/**
	 * Retrieve the data provider definition.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return DataProviderDefinitionInterface|DefaultDataProviderDefinition
	 */
	protected function getDataProviderDefinition(IMetaModelDataDefinition $container)
	{
		// Parse data provider.
		if ($container->hasDataProviderDefinition())
		{
			return $container->getDataProviderDefinition();
		}

		$config = new DefaultDataProviderDefinition();
		$container->setDataProviderDefinition($config);
		return $config;
	}

	/**
	 * Handle a build data definition event for MetaModels.
	 *
	 * @param BuildDataDefinitionEvent $event The event.
	 *
	 * @return void
	 */
	public function build(BuildDataDefinitionEvent $event)
	{
		$this->dispatcher = $event->getDispatcher();

		$container = $event->getContainer();

		if (!($container instanceof IMetaModelDataDefinition))
		{
			return;
		}

		$this->parseMetaModelDefinition($container);
		$this->parseProperties($container);
		$this->parseBasicDefinition($container);
		$this->parseDataProvider($container);
		$this->parseBackendView($container);

		$this->parsePalettes($container);

		// Attach renderer to event.
		RenderItem::register($event->getDispatcher());
	}

	/**
	 * Parse the basic configuration and populate the definition.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return void
	 */
	protected function parseMetaModelDefinition(IMetaModelDataDefinition $container)
	{
		if ($container->hasMetaModelDefinition())
		{
			$definition = $container->getMetaModelDefinition();
		}
		else
		{
			$definition = new MetaModelDefinition();
			$container->setMetaModelDefinition($definition);
		}

		if (!$definition->hasActiveRenderSetting())
		{
			$definition->setActiveRenderSetting(ViewCombinations::getRenderSetting($container->getName()));
		}

		if (!$definition->hasActiveInputScreen())
		{
			$definition->setActiveInputScreen(ViewCombinations::getInputScreen($container->getName()));
		}
	}

	/**
	 * Parse the basic configuration and populate the definition.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return void
	 */
	protected function parseBasicDefinition(IMetaModelDataDefinition $container)
	{
		if ($container->hasBasicDefinition())
		{
			$config = $container->getBasicDefinition();
		}
		else
		{
			$config = new DefaultBasicDefinition();
			$container->setBasicDefinition($config);
		}

		$config->setDataProvider($container->getName());

		$inputScreen = $this->getInputScreenDetails($container);

		switch ($inputScreen->getMode())
		{
			case 0:
			case 1:
			case 2:
			case 3:
				// Flat mode.
				// 0 Records are not sorted.
				// 1 Records are sorted by a fixed field.
				// 2 Records are sorted by a switchable field.
				// 3 Records are sorted by the parent table.
				$config->setMode(BasicDefinitionInterface::MODE_FLAT);
				break;
			case 4:
				// Displays the child records of a parent record (see style sheets module).
				$config->setMode(BasicDefinitionInterface::MODE_PARENTEDLIST);
				break;
			case 5:
			case 6:
				// Hierarchical mode.
				// 5 Records are displayed as tree (see site structure).
				// 6 Displays the child records within a tree structure (see articles module).
				$config->setMode(BasicDefinitionInterface::MODE_HIERARCHICAL);
				break;
			default:
		}

		if (($value = $inputScreen->isClosed()) !== null)
		{
			$config->setClosed((bool)$value);
		}
	}

	/**
	 * Create the data provider definition in the container if not already set.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return void
	 */
	protected function parseDataProvider(IMetaModelDataDefinition $container)
	{
		$config = $this->getDataProviderDefinition($container);

		// Check config if it already exists, if not, add it.
		if (!$config->hasInformation($container->getName()))
		{
			$providerInformation = new ContaoDataProviderInformation();
			$providerInformation->setName($container->getName());
			$config->addInformation($providerInformation);
		}
		else
		{
			$providerInformation = $config->getInformation($container->getName());
		}

		if ($providerInformation instanceof ContaoDataProviderInformation)
		{
			$providerInformation
				->setTableName($container->getName())
				->setClassName('MetaModels\DcGeneral\Data\Driver')
				->setInitializationData(array(
					'source' => $container->getName()
				))
				->isVersioningEnabled(false);
			$container->getBasicDefinition()->setDataProvider($container->getName());
		}
	}

	/**
	 * Parse and build the backend view definition for the old Contao2 backend view.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @throws DcGeneralInvalidArgumentException When the contained view definition is of invalid type.
	 *
	 * @return void
	 */
	protected function parseBackendView(IMetaModelDataDefinition $container)
	{
		if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME))
		{
			$view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
		}
		else
		{
			$view = new Contao2BackendViewDefinition();
			$container->setDefinition(Contao2BackendViewDefinitionInterface::NAME, $view);
		}

		if (!$view instanceof Contao2BackendViewDefinitionInterface)
		{
			throw new DcGeneralInvalidArgumentException(
				'Configured BackendViewDefinition does not implement Contao2BackendViewDefinitionInterface.'
			);
		}

		$this->parseListing($container, $view);
		$this->parseModelOperations($view);
	}


	/**
	 * Parse the listing configuration.
	 *
	 * @param IMetaModelDataDefinition              $container The data container.
	 *
	 * @param Contao2BackendViewDefinitionInterface $view      The view definition.
	 *
	 * @return void
	 */
	protected function parseListing(IMetaModelDataDefinition $container, Contao2BackendViewDefinitionInterface $view)
	{
		$listing = $view->getListingConfig();

		$this->parseListSorting($container, $listing);
		$this->parseListLabel($container, $listing);
	}

	/**
	 * Generate a 16x16 pixel version of the passed image file. If this can not be done, the default image is returned.
	 *
	 * @param string $icon The name of the image file.
	 *
	 * @return null|string
	 */
	public function getBackendIcon($icon)
	{
		// Determine the image to use.
		if ($icon)
		{
			$icon = ToolboxFile::convertValueToPath($icon);

			/** @var ResizeImageEvent $event */
			$event = $this->dispatcher->dispatch(
				ContaoEvents::IMAGE_RESIZE,
				new ResizeImageEvent($icon, 16, 16)
			);

			if (file_exists(TL_ROOT . '/' . $event->getResultImage()))
			{
				return $event->getResultImage();
			}
		}

		return 'system/modules/metamodels/html/metamodels.png';
	}

	/**
	 * Parse the sorting part of listing configuration.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @param ListingConfigInterface   $listing   The listing configuration.
	 *
	 * @return void
	 */
	protected function parseListSorting(IMetaModelDataDefinition $container, ListingConfigInterface $listing)
	{
		$inputScreen = ViewCombinations::getInputScreenDetails($container->getName());

		$listing->setRootIcon($this->getBackendIcon($inputScreen->getIcon()));
	}

	/**
	 * Parse the sorting part of listing configuration.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @param ListingConfigInterface   $listing   The listing config.
	 *
	 * @return void
	 */
	protected function parseListLabel(IMetaModelDataDefinition $container, ListingConfigInterface $listing)
	{
		$providerName = $container->getBasicDefinition()->getDataProvider();
		if (!$listing->hasLabelFormatter($providerName))
		{
			$formatter = new DefaultModelFormatterConfig();
			$listing->setLabelFormatter($container->getBasicDefinition()->getDataProvider(), $formatter);
		}
		else
		{
			$formatter = $listing->getLabelFormatter($providerName);
		}

		$formatter->setPropertyNames(
			array_merge(
				$formatter->getPropertyNames(),
				$container->getPropertiesDefinition()->getPropertyNames()
			)
		);

		if (!$formatter->getFormat())
		{
			$formatter->setFormat(str_repeat('%s ', count($formatter->getPropertyNames())));
		}
	}

	/**
	 * Build a command into the the command collection.
	 *
	 * @param CommandCollectionInterface $collection      The command collection.
	 *
	 * @param string                     $operationName   The operation name.
	 *
	 * @param array                      $queryParameters The query parameters for the operation.
	 *
	 * @param string                     $icon            The icon to use in the backend.
	 *
	 * @param array                      $extraValues     The extra values for the command.
	 *
	 * @return Builder
	 */
	protected function createCommand(
		CommandCollectionInterface $collection,
		$operationName,
		$queryParameters,
		$icon,
		$extraValues
	)
	{
		if ($collection->hasCommandNamed($operationName))
		{
			$command = $collection->getCommandNamed($operationName);
		}
		else
		{
			switch ($operationName)
			{
				case 'cut':
					$command = new CutCommand();
					break;
				default:
					$command = new Command();
			}

			$command->setName($operationName);
			$collection->addCommand($command);
		}

		$parameters = $command->getParameters();
		foreach ($queryParameters as $name => $value)
		{
			if (!isset($parameters[$name]))
			{
				$parameters[$name] = $value;
			}
		}

		if (!$command->getLabel())
		{
			$command->setLabel($operationName . '.0');
		}

		if (!$command->getDescription())
		{
			$command->setDescription($operationName . '.1');
		}

		$extra         = $command->getExtra();
		$extra['icon'] = $icon;

		foreach ($extraValues as $name => $value)
		{
			if (!isset($extra[$name]))
			{
				$extra[$name] = $value;
			}
		}

		return $this;
	}

	/**
	 * Parse the defined model scoped operations and populate the definition.
	 *
	 * @param Contao2BackendViewDefinitionInterface $view The backend view information.
	 *
	 * @return void
	 */
	protected function parseModelOperations(Contao2BackendViewDefinitionInterface $view)
	{
		$collection = $view->getModelCommands();
		$this->createCommand
		(
			$collection,
			'edit',
			array('act' => 'edit'),
			'edit.gif',
			array()
		)
		->createCommand
		(
			$collection,
			'copy',
			array('act' => ''),
			'copy.gif',
			array('attributes' => 'onclick="Backend.getScrollOffset();"')
		)
		->createCommand
		(
			$collection,
			'cut',
			array('act' => 'paste', 'mode' => 'cut'),
			'cut.gif',
			array(
				'attributes' => 'onclick="Backend.getScrollOffset();"'
			)
		)
		->createCommand
		(
			$collection,
			'delete',
			array('act' => 'delete'),
			'delete.gif',
			array(
				'attributes' => sprintf(
					'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
					// FIXME: we need the translation manager here.
					$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
				)
			)
		)
		->createCommand
		(
			$collection,
			'show',
			array('act' => 'show'),
			'show.gif',
			array()
		);
	}

	protected function buildPropertyFromDca(
		IMetaModelDataDefinition $container,
		PropertiesDefinitionInterface $definition,
		$propName,
		IInputScreen $inputScreen
	)
	{
		$property = $inputScreen->getProperty($propName);
		$propInfo = $property['info'];

		if ($definition->hasProperty($propName))
		{
			$property = $definition->getProperty($propName);
		}
		else
		{
			$property = new DefaultProperty($propName);
			$definition->addProperty($property);
		}

		if (!$property->getLabel() && isset($propInfo['label']))
		{
			$lang = $propInfo['label'];

			if (is_array($lang))
			{
				$label       = reset($lang);
				$description = next($lang);

				$property->setDescription($description);
			}
			else {
				$label = $lang;
			}

			$property->setLabel($label);
		}

		if (!$property->getDescription() && isset($propInfo['description']))
		{
			$property->setDescription($propInfo['description']);
		}

		if (!$property->getDefaultValue() && isset($propInfo['default']))
		{
			$property->setDefaultValue($propInfo['default']);
		}

		if (isset($propInfo['exclude']))
		{
			$property->setExcluded($propInfo['exclude']);
		}

		if (isset($propInfo['search']))
		{
			$property->setSearchable($propInfo['search']);
		}

		if (isset($propInfo['sorting']))
		{
			$property->setSortable($propInfo['sorting']);
		}

		if (isset($propInfo['filter']))
		{
			$property->setFilterable($propInfo['filter']);
		}

		if (!$property->getGroupingLength() && isset($propInfo['length']))
		{
			$property->setGroupingLength($propInfo['length']);
		}

		if (!$property->getWidgetType() && isset($propInfo['inputType']))
		{
			$property->setWidgetType($propInfo['inputType']);
		}

		if (!$property->getOptions() && isset($propInfo['options']))
		{
			$property->setOptions($propInfo['options']);
		}

		if (!$property->getExplanation() && isset($propInfo['explanation']))
		{
			$property->setExplanation($propInfo['explanation']);
		}

		if (!$property->getExtra() && isset($propInfo['eval']))
		{
			$property->setExtra($propInfo['eval']);
		}
	}

	/**
	 * Parse the defined properties and populate the definition.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return void
	 */
	protected function parseProperties(IMetaModelDataDefinition $container)
	{
		if ($container->hasPropertiesDefinition())
		{
			$definition = $container->getPropertiesDefinition();
		}
		else
		{
			$definition = new DefaultPropertiesDefinition();
			$container->setPropertiesDefinition($definition);
		}

		$metaModel   = Factory::byTableName($container->getName());
		$inputScreen = $this->getInputScreenDetails($container);

		foreach ($metaModel->getAttributes() as $attribute)
		{
			$this->buildPropertyFromDca($container, $definition, $attribute->getColName(), $inputScreen);
		}
	}

	/**
	 * Add a PropertyTrueCondition to the condition of the sub palette parent property if parent property is defined.
	 *
	 * @param string          $parentPropertyName The name of the parent property.
	 *
	 * @param array           $propInfo           The property definition from the dca.
	 *
	 * @param LegendInterface $paletteLegend      The legend where the property is contained.
	 *
	 * @return void
	 */
	protected function addSubPalette($parentPropertyName, $propInfo, LegendInterface $paletteLegend)
	{
		if ($propInfo['subpalette'])
		{
			foreach ($propInfo['subpalette'] as $propertyName)
			{
				$property = new Property($propertyName);
				$paletteLegend->addProperty($property);
				$property->setVisibleCondition(new PropertyTrueCondition($parentPropertyName));
			}
		}
	}

	/**
	 * Parse the palettes from the input screen into the data container.
	 *
	 * @param IMetaModelDataDefinition $container The data container.
	 *
	 * @return void
	 */
	protected function parsePalettes(IMetaModelDataDefinition $container)
	{
		$inputScreen = $this->getInputScreenDetails($container);
		$metaModel   = $this->getMetaModel($container);

		if ($container->hasDefinition(PalettesDefinitionInterface::NAME))
		{
			$palettesDefinition = $container->getDefinition(PalettesDefinitionInterface::NAME);
		}
		else
		{
			$palettesDefinition = new DefaultPalettesDefinition();
			$container->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
		}

		$palette = new Palette();
		$palette
			->setName('default')
			->setCondition(new DefaultPaletteCondition());
		$palettesDefinition->addPalette($palette);

		foreach ($inputScreen->getLegends() as $legendName => $legend)
		{
			$paletteLegend = new Legend($legendName);
			$paletteLegend->setInitialVisibility($legend['visible']);
			$palette->addLegend($paletteLegend);

			$this->translator->setValue($legendName . '_legend', $legend['name'], $container->getName());

			foreach ($legend['properties'] as $propertyName)
			{
				$property = new Property($propertyName);
				$paletteLegend->addProperty($property);
				$propInfo = $inputScreen->getProperty($propertyName);

				$chain = new PropertyConditionChain();
				$property->setEditableCondition($chain);

				$chain->addCondition(new BooleanCondition(
					!(isset($propInfo['info']['readonly']) && $propInfo['info']['readonly'])
				));

				if ($metaModel->hasVariants() && !$metaModel->getAttribute($propertyName)->get('isvariant'))
				{
					$chain->addCondition(new PropertyValueCondition('varbase', 1));
				}

				$extra = $propInfo['info'];
				$property->setVisibleCondition(new BooleanCondition(
					!((isset($extra['doNotShow']) && $extra['doNotShow'])
					|| (isset($extra['hideInput']) && $extra['hideInput']))
				));

				$this->addSubPalette($propertyName, $propInfo, $paletteLegend);
			}
		}
	}
}
