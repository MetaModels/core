<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Alexander Menk <a.menk@imi.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Dca\Builder;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DefinitionBuilder\BasicDefinitionBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\CommandBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\Contao2BackendViewDefinitionBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\DataProviderBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\MetaModelDefinitionBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PaletteBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PanelBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PropertyDefinitionBuilder;
use MetaModels\DcGeneral\Events\MetaModel\RenderItem;
use MetaModels\Helper\ToolboxFile;
use MetaModels\IMetaModelsServiceContainer;

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
    private $translator;

    /**
     * The MetaModel this builder is responsible for.
     *
     * @var IMetaModelsServiceContainer
     */
    private $serviceContainer;

    /**
     * The input screen to use.
     *
     * @var IInputScreen
     */
    private $inputScreen;

    /**
     * Create a new instance and instantiate the translator.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The name of the MetaModel being created.
     *
     * @param IInputScreen                $inputScreen      The input screen to use.
     *
     * @param int                         $renderSetting    The render setting.
     */
    public function __construct($serviceContainer, $inputScreen, $renderSetting)
    {
        $this->serviceContainer = $serviceContainer;
        $this->inputScreen      = $inputScreen;
        $this->translator       = new StaticTranslator();
        $this->renderSetting    = $renderSetting;
    }

    /**
     * Retrieve the translator.
     *
     * @return StaticTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
    /**
     * Retrieve the MetaModel.
     *
     * @return ViewCombinations|null
     */
    protected function getViewCombinations()
    {
        return $this->serviceContainer->getService('metamodels-view-combinations');
    }

    /**
     * Handle a build data definition event for MetaModels.
     *
     * @param BuildDataDefinitionEvent $event The event payload.
     *
     * @return void
     */
    public function build(BuildDataDefinitionEvent $event)
    {
        $dispatcher = $this->serviceContainer->getEventDispatcher();
        $container  = $event->getContainer();
        /** @var $container IMetaModelDataDefinition */
        $builder = new MetaModelDefinitionBuilder($this->getViewCombinations());
        $builder->build($container);

        $builder = new PropertyDefinitionBuilder($dispatcher);
        $builder->build($container, $this->inputScreen, $this);

        $builder = new BasicDefinitionBuilder($dispatcher);
        $builder->build($container, $this->inputScreen);

        $dataBuilder = new DataProviderBuilder($this->inputScreen, $this->serviceContainer->getFactory());
        $dataBuilder->parseDataProvider($container);

        $builder = new Contao2BackendViewDefinitionBuilder($dispatcher, $this->serviceContainer->getRenderSettingFactory());
        $builder->build($container, $this->inputScreen);

        $builder = new CommandBuilder($dispatcher, $this->getViewCombinations());
        $builder->build($container, $this->inputScreen, $this);
        $builder = new PanelBuilder($this->inputScreen);
        $builder->build($container);

        $builder = new PaletteBuilder();
        $builder->build($container, $this->inputScreen, $this->translator);

        // Attach renderer to event.
        RenderItem::register($dispatcher);
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
        if ($icon) {
            $icon = ToolboxFile::convertValueToPath($icon);

            /** @var ResizeImageEvent $event */
            $event = $this->serviceContainer->getEventDispatcher()->dispatch(
                ContaoEvents::IMAGE_RESIZE,
                new ResizeImageEvent($icon, 16, 16)
            );

            if (file_exists(TL_ROOT . '/' . $event->getResultImage())) {
                return $event->getResultImage();
            }
        }

        return 'system/modules/metamodels/assets/images/icons/metamodels.png';
    }
}
