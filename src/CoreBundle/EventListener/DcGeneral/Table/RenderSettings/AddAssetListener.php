<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Andreas Fischer <anfischer@kaffee-partner.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;
use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

/**
 * This handles the rendering of models to labels.
 */
class AddAssetListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * The upload path to scan within.
     *
     * @var string
     */
    private $uploadPath;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param string                   $uploadPath        The upload path to scan within.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, $uploadPath)
    {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->uploadPath        = $uploadPath;
    }

    /**
     * Provide options for additional css files.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getStylesheets(GetOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)
            || ($event->getPropertyName() !== 'additionalCss')
            || ($event->getSubPropertyName() !== 'file')) {
            return;
        }

        $event->setOptions($this->scanFiles('css'));
    }

    /**
     * Provide options for additional javascript files.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getJavascripts(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersettings')
            || ($event->getPropertyName() !== 'additionalJs')
            || ($event->getSubPropertyName() !== 'file')) {
            return;
        }

        $event->setOptions($this->scanFiles('js'));
    }

    /**
     * Scan for files with the given extension.
     *
     * @param string $extension The file extension.
     *
     * @return array
     */
    private function scanFiles($extension)
    {
        $files = [];
        foreach (Finder::create()->followLinks()->in($this->uploadPath)->name('*.' . $extension)->getIterator() as $item) {
            $files[] = 'files/' . Path::normalize($item->getRelativePathname());
        }

        return $files;
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param GetOptionsEvent $event The event to test.
     *
     * @return bool
     */
    private function wantToHandle(GetOptionsEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();
        if ('tl_metamodel_rendersettings' !== $environment->getDataDefinition()->getName()) {
            return false;
        }

        if ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName()) {
            return false;
        }

        return true;
    }
}
