<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\Assets;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\Validator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class takes care of building icons for the backend.
 */
class IconBuilder
{
    /**
     * The root path of the application.
     *
     * @var string
     */
    private $rootPath;

    /**
     * The output path for assets.
     *
     * @var string
     */
    private $outputPath;

    /**
     * The web reachable path for assets.
     *
     * @var string
     */
    private $webPath;

    /**
     * Adapter to the Contao\FilesModel class.
     *
     * @var \Contao\FilesModel
     */
    private $filesAdapter;

    /**
     * The image factory.
     *
     * @var ImageFactoryInterface
     */
    private $imageFactory;

    /**
     * The image adapter.
     *
     * @var \Contao\Image
     */
    private $image;

    /**
     * Create a new instance.
     *
     * @param Adapter               $filesAdapter Adapter to the Contao files model class.
     * @param ImageFactoryInterface $imageFactory The image factory for resizing images.
     * @param string                $rootPath     The root path of the application.
     * @param string                $outputPath   The output path for assets.
     * @param string                $webPath      The web reachable path for assets.
     * @param Adapter               $imageAdapter The image adapter to generate HTML code images.
     */
    public function __construct(
        Adapter $filesAdapter,
        ImageFactoryInterface $imageFactory,
        $rootPath,
        $outputPath,
        $webPath,
        Adapter $imageAdapter
    ) {
        $this->filesAdapter = $filesAdapter;
        $this->imageFactory = $imageFactory;
        $this->rootPath     = $rootPath;
        $this->outputPath   = $outputPath;
        $this->webPath      = $webPath;
        $this->image        = $imageAdapter;

        // Ensure output path exists.
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->outputPath);
    }

    /**
     * Get a 16x16 pixel resized icon of the passed image if it exists, return the default icon otherwise.
     *
     * @param string $icon        The icon to resize.
     * @param string $defaultIcon The default icon.
     *
     * @return string
     */
    public function getBackendIcon($icon, $defaultIcon = 'bundles/metamodelscore/images/icons/metamodels.png')
    {
        $realIcon = $this->convertValueToPath($icon, $defaultIcon);

        return $this->imageFactory->create(
            $realIcon,
            [16, 16, 'proportional'],
            $this->outputPath . '/' . basename($realIcon)
        )->getPath();
    }

    /**
     * Get a 16x16 pixel resized icon <img>-tag of the passed image if it exists, return the default MM icon otherwise.
     *
     * @param string $icon        The image path.
     * @param string $alt         An optional alt attribute.
     * @param string $attributes  A string of other attributes.
     * @param string $defaultIcon The default icon.
     *
     * @return string
     */
    public function getBackendIconImageTag(
        $icon,
        $alt = '',
        $attributes = '',
        $defaultIcon = 'bundles/metamodelscore/images/icons/metamodels.png'
    ) {
        return $this->image->getHtml(
            $this->webPath . '/' . substr($this->getBackendIcon($icon, $defaultIcon), strlen($this->outputPath) + 1),
            $alt,
            $attributes
        );
    }

    /**
     * Translate the file ID to file path.
     *
     * @param string $varValue The file id.
     * @param string $fallback The fallback file path.
     *
     * @return string
     */
    public function convertValueToPath($varValue, $fallback)
    {
        if (Validator::isUuid($varValue)) {
            $model = $this->filesAdapter->findByPk($varValue);
            if ($model !== null && file_exists($this->rootPath . '/' . $model->path)) {
                return $model->path;
            }
            return $fallback;
        }
        if (file_exists($varValue)) {
            return $varValue;
        }

        return $fallback;
    }
}
