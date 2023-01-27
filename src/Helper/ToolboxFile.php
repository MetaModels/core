<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Andreas Fischer <anfischer@kaffee-partner.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Contao\Controller;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Input;
use Contao\LayoutModel;
use Contao\PageError403;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use InvalidArgumentException;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * This class provides various methods for handling file collection within Contao.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class ToolboxFile
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     *
     * @deprecated The event dispatcher will get removed in 3.0 as we now use the image factory.
     */
    private EventDispatcherInterface|null $dispatcher;

    /**
     * The project root dir.
     *
     * @var string|null
     */
    private string|null $rootDir;

    /**
     * The image factory for resizing.
     *
     * @var ImageFactoryInterface|null
     */
    private ImageFactoryInterface|null $imageFactory;

    /**
     * The assets file context.
     *
     * @var ContextInterface|null
     */
    private ContextInterface|null $filesContext;

    /**
     * The picture factory.
     *
     * @var PictureFactoryInterface|null
     */
    private PictureFactoryInterface|null $pictureFactory;

    /**
     * Symfony session object
     *
     * @var Session
     */
    private Session $session;

    /**
     * Allowed file extensions.
     *
     * @var array
     */
    protected array $acceptedExtensions = [];

    /**
     * Base language, used for retrieving meta.txt information.
     *
     * @var string
     */
    protected string $baseLanguage = '';

    /**
     * The fallback language, used for retrieving meta.txt information.
     *
     * @var string|null
     */
    protected string|null $fallbackLanguage = '';

    /**
     * Determines if we want to generate images or not.
     *
     * @var bool|null
     */
    protected bool|null $blnShowImages = false;

    /**
     * Image resize information.
     *
     * @var array
     */
    protected array $resizeImages = [];

    /**
     * The id to use in lightboxes.
     *
     * @var string
     */
    protected string $strLightboxId = '';

    /**
     * The files to process in this instance.
     *
     * @var array
     */
    protected array $foundFiles = [];

    /**
     * The pending paths to collect from DB.
     *
     * @var string[]
     */
    protected array $pendingPaths = [];

    /**
     * The pending uuids to collect from DB.
     *
     * @var array
     */
    protected array $pendingIds = [];

    /**
     * Flag if download keys shall be generated.
     *
     * @var bool
     */
    private bool $withDownloadKeys = true;

    /**
     * Meta information for files.
     *
     * @var array
     */
    protected array $metaInformation = [];

    /**
     * File id mapping for files.
     *
     * @var string[]
     */
    protected array $uuidMap = [];

    /**
     * Buffered file information.
     *
     * @var array
     */
    protected array $outputBuffer = [];

    /**
     * Buffered modification timestamps.
     *
     * @var array
     */
    protected array $modifiedTime = [];

    /**
     * Create a new instance.
     *
     * @param ImageFactoryInterface|EventDispatcherInterface|null $imageFactory   The image factory to use.
     * @param string|null                                         $rootDir        The root path of the installation.
     * @param ContextInterface|null                               $filesContext   The assets file context.
     * @param PictureFactoryInterface|null                        $pictureFactory The picture factory.
     * @param Session|null                                        $session        The session.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        ImageFactoryInterface|EventDispatcherInterface $imageFactory = null,
        string $rootDir = null,
        ContextInterface $filesContext = null,
        PictureFactoryInterface $pictureFactory = null,
        Session $session = null
    ) {
        switch (true) {
            case ($imageFactory instanceof ImageFactoryInterface) && !empty($rootDir):
                $this->imageFactory = $imageFactory;
                $this->rootDir      = $rootDir;
                break;
            // This is the deprecated fallback (remove in MetaModels 3.0).
            case $imageFactory instanceof EventDispatcherInterface:
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Passing an "EventDispatcherInterface" is deprecated, use a "ImageFactoryInterface" instead.',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
                $this->dispatcher = $imageFactory;
                break;
            // This is another deprecated fallback (remove in MetaModels 3.0).
            default:
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Not passing an "ImageFactoryInterface" and root path is deprecated.',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
                $this->dispatcher = System::getContainer()->get('event_dispatcher');
        }
        // Initialize some values to sane base.
        if (isset($GLOBALS['TL_CONFIG']) && isset($GLOBALS['TL_CONFIG']['allowedDownload'])) {
            $this->setAcceptedExtensions(StringUtil::trimsplit(',', $GLOBALS['TL_CONFIG']['allowedDownload']));
        }

        if (null === ($this->rootDir)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing an "%kernel.project_dir%" parameter is deprecated.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->rootDir = System::getContainer()->getParameter('kernel.project_dir');
        }

        if (null === ($this->filesContext = $filesContext)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing an "ContextInterface" is deprecated.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->filesContext = System::getContainer()->get('contao.assets.files_context');
        }

        if (null === ($this->pictureFactory = $pictureFactory)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing an "PictureFactoryInterface" is deprecated.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $this->pictureFactory = System::getContainer()->get('contao.image.picture_factory');
        }

        if (null === $session) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing a "Session" is deprecated.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $session = System::getContainer()->get('session');
            assert($session instanceof Session);
        }

        $this->session = $session;
    }

    /**
     * Set the allowed file extensions.
     *
     * @param string|array $acceptedExtensions The list of accepted file extensions.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function setAcceptedExtensions($acceptedExtensions)
    {
        // We must not allow file extensions that are globally disabled.
        $allowedDownload = StringUtil::trimsplit(',', $GLOBALS['TL_CONFIG']['allowedDownload']);

        if (!\is_array($acceptedExtensions)) {
            $acceptedExtensions = StringUtil::trimsplit(',', $acceptedExtensions);
        }

        $this->acceptedExtensions = \array_map('strtolower', \array_intersect($allowedDownload, $acceptedExtensions));
    }

    /**
     * Retrieve the allowed file extensions.
     *
     * @return array
     */
    public function getAcceptedExtensions()
    {
        return $this->acceptedExtensions;
    }

    /**
     * Set the base language.
     *
     * @param string $baseLanguage The base language to use.
     *
     * @return ToolboxFile
     */
    public function setBaseLanguage($baseLanguage)
    {
        $this->baseLanguage = $baseLanguage;

        return $this;
    }

    /**
     * Retrieve the base language.
     *
     * @return string
     */
    public function getBaseLanguage()
    {
        return $this->baseLanguage;
    }

    /**
     * Set the fallback language.
     *
     * @param string|null $fallbackLanguage The fallback language to use.
     *
     * @return ToolboxFile
     */
    public function setFallbackLanguage(?string $fallbackLanguage): static
    {
        $this->fallbackLanguage = $fallbackLanguage;

        return $this;
    }

    /**
     * Retrieve the fallback language.
     *
     * @return string|null
     */
    public function getFallbackLanguage(): ?string
    {
        return $this->fallbackLanguage;
    }

    /**
     * Set to show/prepare images or not.
     *
     * @param boolean|null $blnShowImages True to show images, false otherwise.
     *
     * @return ToolboxFile
     */
    public function setShowImages(?bool $blnShowImages): static
    {
        $this->blnShowImages = $blnShowImages;

        return $this;
    }

    /**
     * Retrieve the flag if images shall be rendered as images.
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShowImages()
    {
        return $this->blnShowImages;
    }

    /**
     * Set the resize information.
     *
     * @param array $resizeImages The resize information. Array of 3 elements: 0: Width, 1: Height, 2: Mode.
     *
     * @return ToolboxFile
     */
    public function setResizeImages($resizeImages)
    {
        $this->resizeImages = $resizeImages;

        return $this;
    }

    /**
     * Retrieve the resize information.
     *
     * @return array
     */
    public function getResizeImages()
    {
        return $this->resizeImages;
    }

    /**
     * Sets the Id to use for the lightbox.
     *
     * @param string $strLightboxId The lightbox id to use.
     *
     * @return ToolboxFile
     */
    public function setLightboxId($strLightboxId)
    {
        $this->strLightboxId = $strLightboxId;

        return $this;
    }

    /**
     * Retrieve the lightbox id to use.
     *
     * @return string
     */
    public function getLightboxId()
    {
        return $this->strLightboxId;
    }

    /**
     * Add path to file or folder list.
     *
     * @param string $strPath The path to be added.
     *
     * @return ToolboxFile
     */
    public function addPath($strPath)
    {
        $this->pendingPaths[] = $strPath;

        return $this;
    }

    /**
     * Contao 3 DBAFS Support.
     *
     * @param string $strId String uuid of the file.
     *
     * @return ToolboxFile
     */
    public function addPathById($strId)
    {
        // Check if empty.
        if (empty($strId)) {
            return $this;
        }

        if (!Validator::isBinaryUuid($strId)) {
            $this->pendingIds[] = StringUtil::uuidToBin($strId);
            return $this;
        }

        $this->pendingIds[] = $strId;
        return $this;
    }

    /**
     * Set flag if download keys shall be generated or not.
     *
     * @param bool $withDownloadKeys The new value.
     *
     * @return void
     */
    public function withDownloadKeys(bool $withDownloadKeys): void
    {
        $this->withDownloadKeys = $withDownloadKeys;
    }

    /**
     * Walks the list of pending folders via ToolboxFile::addPath().
     *
     * @return void
     */
    protected function collectFiles()
    {
        $table = FilesModel::getTable();

        $conditions = [];
        $parameters = [];
        if (\count($this->pendingIds)) {
            $conditions[] = $table . '.uuid IN(' .
                            \implode(',', \array_fill(0, count($this->pendingIds), 'UNHEX(?)')) . ')';
            $parameters   = \array_map('bin2hex', $this->pendingIds);

            $this->pendingIds = [];
        }
        if (\count($this->pendingPaths)) {
            $slug = $table . '.path LIKE ?';
            foreach ($this->pendingPaths as $pendingPath) {
                $conditions[] = $slug;
                $parameters[] = $pendingPath . '%';
            }
            $this->pendingPaths = [];
        }

        if (!\count($conditions)) {
            return;
        }

        if ($files = FilesModel::findBy([\implode(' OR ', $conditions)], $parameters)) {
            $this->addFileModels($files);
        }

        if (count($this->pendingPaths)) {
            // Run again.
            $this->collectFiles();
        }
    }

    /**
     * Generate a URL for downloading the given file.
     *
     * @param string $strFile The file that shall be downloaded.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDownloadLink($strFile)
    {
        if (!$this->withDownloadKeys) {
            return UrlBuilder::fromUrl(Environment::get('request'))
                ->setQueryParameter('file', \urlencode($strFile))
                ->getUrl();
        }

        $bag = $this->session->getBag('attributes');

        $links = $bag->has('metaModels_downloads') ? $bag->get('metaModels_downloads') : [];
        if (!\is_array($links)) {
            $links = [];
        }
        if (!isset($links[$strFile])) {
            $links[$strFile] = \md5(\uniqid('', true));
            $bag->set('metaModels_downloads', $links);
        }

        return UrlBuilder::fromUrl(Environment::get('request'))
            ->setQueryParameter('file', urlencode($strFile))
            ->setQueryParameter('fileKey', $links[$strFile])
            ->getUrl();
    }

    /**
     * Walk all files and fetch desired additional information like image sizes etc.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function fetchAdditionalData()
    {
        $this->modifiedTime = [];
        $this->outputBuffer = [];

        if (!$this->foundFiles) {
            return;
        }

        foreach ($this->foundFiles as $strFile) {
            $this->processFile($strFile);
        }
    }

    /**
     * Maps the sorting from the files to the source.
     *
     * All files from $arrFiles are being walked and the corresponding entry from source gets pulled in.
     *
     * Additionally, the css classes are applied to the returned 'source' array.
     *
     * This returns an array like: array('files' => array(), 'source' => array())
     *
     * @param array $arrFiles  The files to sort.
     *
     * @param array $arrSource The source list.
     *
     * @return array The mapped result.
     */
    protected function remapSorting($arrFiles, $arrSource)
    {
        $files  = [];
        $source = [];

        foreach (\array_keys($arrFiles) as $k) {
            $files[]  = $arrFiles[$k];
            $source[] = $arrSource[$k];
        }

        $this->addClasses($source);

        return [
            'files'  => $files,
            'source' => $source
        ];
    }

    /**
     * Sorts the internal file list by a given condition.
     *
     * Allowed sort types are:
     * name_asc  - Sort by filename ascending.
     * name_desc - Sort by filename descending
     * date_asc  - Sort by modification time ascending.
     * date_desc - Sort by modification time descending.
     * manual    - Sort by passed id array, the array must contain the binary ids of the files.
     * random    - Shuffle all the files around.
     *
     * @param string $sortType The sort condition to be applied.
     *
     * @param array  $sortIds  The list of binary ids to sort by (sort type "manual" only).
     *
     * @return array The sorted file list.
     */
    public function sortFiles($sortType, $sortIds = [])
    {
        switch ($sortType) {
            case 'name_desc':
                return $this->sortByName(false);

            case 'date_asc':
                return $this->sortByDate(true);

            case 'date_desc':
                return $this->sortByDate(false);

            case 'manual':
                return $this->sortByIdList($sortIds);

            case 'random':
                return $this->sortByRandom();

            default:
            case 'name_asc':
        }
        return $this->sortByName(true);
    }

    /**
     * Attach first, last and even/odd classes to the given array.
     *
     * @param array $arrSource The array reference of the array to which the classes shall be added to.
     *
     * @return void
     */
    protected function addClasses(&$arrSource)
    {
        $countFiles = \count($arrSource);
        foreach (\array_keys($arrSource) as $k) {
            $arrSource[$k]['class'] = (($k == 0) ? ' first' : '') .
                                      (($k == ($countFiles - 1)) ? ' last' : '') .
                                      ((($k % 2) == 0) ? ' even' : ' odd');
        }
    }

    /**
     * Sort by filename.
     *
     * @param boolean $blnAscending Flag to determine if sorting shall be applied ascending (default) or descending.
     *
     * @return array
     */
    protected function sortByName($blnAscending = true)
    {
        $arrFiles = $this->foundFiles;

        if (!$arrFiles) {
            return ['files' => [], 'source' => []];
        }

        \uasort($arrFiles, ($blnAscending) ? '\basename_natcasecmp' : '\basename_natcasercmp');

        return $this->remapSorting($arrFiles, $this->outputBuffer);
    }

    /**
     * Sort by modification time.
     *
     * @param boolean $blnAscending Flag to determine if sorting shall be applied ascending (default) or descending.
     *
     * @return array
     */
    protected function sortByDate($blnAscending = true)
    {
        $arrFiles = $this->foundFiles;
        $arrDates = $this->modifiedTime;

        if (!$arrFiles) {
            return ['files' => [], 'source' => []];
        }

        if ($blnAscending) {
            \array_multisort($arrFiles, SORT_NUMERIC, $arrDates, SORT_ASC);
        } else {
            \array_multisort($arrFiles, SORT_NUMERIC, $arrDates, SORT_DESC);
        }

        return $this->remapSorting($arrFiles, $this->outputBuffer);
    }

    /**
     * Sort by passed id list.
     *
     * @param array $sortIds The list of binary ids to sort by.
     *
     * @return array
     */
    protected function sortByIdList($sortIds)
    {
        $fileMap = $this->foundFiles;
        if (!$fileMap) {
            return ['files' => [], 'source' => []];
        }
        $fileKeys = \array_flip(\array_keys($this->uuidMap));
        $sorted   = [];
        foreach ($sortIds as $sortStringId) {
            $key          = $fileKeys[$sortStringId];
            $sorted[$key] = $fileMap[$key];
            unset($fileMap[$key]);
        }
        // Add anything not sorted yet to the end.
        $sorted += $fileMap;

        return $this->remapSorting($sorted, $this->outputBuffer);
    }

    /**
     * Shuffle the file list.
     *
     * @return array
     */
    protected function sortByRandom()
    {
        $arrFiles  = $this->foundFiles;
        $arrSource = $this->outputBuffer;

        if (!$arrFiles) {
            return ['files' => [], 'source' => []];
        }

        $keys  = \array_keys($arrFiles);
        $files = [];
        \shuffle($keys);
        foreach ($keys as $key) {
            $files[$key] = $arrFiles[$key];
        }

        return $this->remapSorting($files, $arrSource);
    }

    /**
     * Returns the file list.
     *
     * NOTE: you must call resolveFiles() beforehand as otherwise folders are not being evaluated.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->foundFiles;
    }

    /**
     * Process all folders and resolve to a valid file list.
     *
     * @return ToolboxFile
     */
    public function resolveFiles()
    {
        // Step 1.: fetch all files.
        $this->collectFiles();

        // Step 1.1.: Check if any file is to be served.
        $this->checkDownloads();

        // Step 2.: fetch additional information like modification time etc. and prepare the output buffer.
        $this->fetchAdditionalData();

        return $this;
    }

    /**
     * Check if a file download is desired.
     *
     * See https://github.com/MetaModels/attribute_file/issues/6
     * See https://github.com/MetaModels/core/issues/1014
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function checkDownloads()
    {
        // If images are to be shown, get out.
        if ($this->getShowImages()) {
            return;
        }

        if (($file = Input::get('file'))) {
            if ($this->withDownloadKeys) {
                $bag   = $this->session->getBag('attributes');
                $links = $bag->has('metaModels_downloads') ? $bag->get('metaModels_downloads') : [];
                if (!\is_array($links)) {
                    $links = [];
                }
                // Check key and return 403 if mismatch
                // keep both null-coalescing values different to account for missing values.
                if (($links[$file] ?? null) !== (Input::get('fileKey') ?? false)) {
                    $objHandler = new $GLOBALS['TL_PTY']['error_403']();
                    /** @var PageError403 $objHandler */
                    $objHandler->generate($file);
                }
            }
            // Send the file to the browser if check succeeded.
            Controller::sendFileToBrowser($file);
        }
    }

    /**
     * Translate the file ID to file path.
     *
     * @param string $varValue The file id.
     *
     * @return string
     */
    public static function convertValueToPath($varValue)
    {
        if (empty($varValue)) {
            return '';
        }

        $objFiles = FilesModel::findByPk($varValue);

        if ($objFiles !== null) {
            return $objFiles->path;
        }
        return '';
    }

    /**
     * Convert an array of values handled by MetaModels to a value to be stored in the database (array of bin uuid).
     *
     * The input array must have the following layout:
     * [
     *   'bin'   => [] // list of the binary ids.
     *   'value' => [] // list of the uuids.
     *   'path'  => [] // list of the paths.
     *   'meta'  => [] // list of the meta data.
     * ]
     *
     * @param array $values The values to convert.
     *
     * @return array
     *
     * @throws InvalidArgumentException When the input array is invalid.
     */
    public static function convertValuesToDatabase($values)
    {
        if (!(isset($values['bin']) && isset($values['value']) && isset($values['path']))) {
            throw new InvalidArgumentException('Invalid file array');
        }

        $bin = [];
        foreach ($values['bin'] as $value) {
            $bin[] = $value;
        }

        return $bin;
    }

    /**
     * Convert an array of values stored in the database (array of bin uuid) to a value to be handled by MetaModels.
     *
     * The output array will have the following layout:
     * [
     *   'bin'   => [] // list of the binary ids.
     *   'value' => [] // list of the uuids.
     *   'path'  => [] // list of the paths.
     *   'meta'  => [] // list of the meta data.
     * ]
     *
     * @param array $values The binary uuid values to convert.
     *
     * @return array
     *
     * @throws InvalidArgumentException When the input array is invalid.
     */
    public static function convertValuesToMetaModels($values)
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException('Invalid uuid list.');
        }

        // Convert UUIDs to binary and clean empty values out.
        $values = \array_filter(
            \array_map(function ($fileId) {
                return Validator::isStringUuid($fileId) ? StringUtil::uuidToBin($fileId) : $fileId;
            }, $values)
        );

        $result = [
            'bin'   => [],
            'value' => [],
            'path'  => [],
            'meta'  => []
        ];
        if (empty($values)) {
            return $result;
        }

        $models = FilesModel::findMultipleByUuids($values);

        if ($models === null) {
            return $result;
        }

        foreach ($models as $value) {
            $result['bin'][]   = $value->uuid;
            $result['value'][] = StringUtil::binToUuid($value->uuid);
            $result['path'][]  = $value->path;
            $result['meta'][]  = StringUtil::deserialize($value->meta, true);
        }

        return $result;
    }

    /**
     * Convert an uuid or path to a value to be handled by MetaModels.
     *
     * The output array will have the following layout:
     * [
     *   'bin'   => [] // list of the binary ids.
     *   'value' => [] // list of the uuids.
     *   'path'  => [] // list of the paths.
     *   'meta'  => [] // list of the meta data.
     * ]
     *
     * @param array $values The binary uuids or paths to convert.
     *
     * @return array
     *
     * @throws InvalidArgumentException When any of the input is not a valid uuid or an non existent file.
     */
    public static function convertUuidsOrPathsToMetaModels($values)
    {
        $values = \array_filter((array) $values);
        if (empty($values)) {
            return [
                'bin'   => [],
                'value' => [],
                'path'  => [],
                'meta'  => []
            ];
        }

        foreach ($values as $key => $value) {
            if (!(Validator::isUuid($value))) {
                if (!\is_string($value)) {
                    continue;
                }

                $file = FilesModel::findByPath($value) ?: Dbafs::addResource($value);
                if (!$file) {
                    throw new InvalidArgumentException('Invalid path.');
                }

                $values[$key] = $file->uuid;
            }
        }

        return self::convertValuesToMetaModels($values);
    }

    /**
     * Add the passed file model collection to the current buffer if the extension is allowed.
     *
     * Must either be called from within collectFiles or collectFiles must be called later on as this method
     * will add models of type folder to the list of pending paths to allow for recursive inclusion.
     *
     * @param FilesModel[] $files     The files to add.
     * @param array        $skipPaths List of directories not to be added to the list of pending directories.
     *
     * @return void
     */
    private function addFileModels($files, $skipPaths = [])
    {
        $baseLanguage     = $this->getBaseLanguage();
        $fallbackLanguage = $this->getFallbackLanguage();
        foreach ($files as $file) {
            if ('folder' === $file->type && !\in_array($file->path, $skipPaths)) {
                $this->pendingPaths[] = $file->path . '/';
                continue;
            }
            if (is_file(TL_ROOT . DIRECTORY_SEPARATOR . $file->path)
                && \in_array(
                    \strtolower(pathinfo($file->path, PATHINFO_EXTENSION)),
                    $this->acceptedExtensions
                )
            ) {
                $path                       = $file->path;
                $this->foundFiles[]         = $path;
                $this->uuidMap[$file->uuid] = $path;
                $meta                       = StringUtil::deserialize($file->meta, true);

                if (isset($meta[$baseLanguage])) {
                    $this->metaInformation[dirname($path)][basename($path)] = $meta[$baseLanguage];
                } elseif (isset($meta[$fallbackLanguage])) {
                    $this->metaInformation[dirname($path)][basename($path)] = $meta[$fallbackLanguage];
                }
            }
        }
    }

    /**
     * Process a single file.
     *
     * @param string $fileName The file to fetch data for.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function processFile($fileName)
    {
        $file  = new File($fileName);
        $meta  = $this->metaInformation[dirname($fileName)][$file->basename] ?? [];
        $title = isset($meta['title']) && \strlen($meta['title'])
            ? $meta['title']
            : StringUtil::specialchars($file->basename);
        if (isset($meta['caption']) && \strlen($meta['caption'])) {
            $altText = $meta['caption'];
        } else {
            $altText = \ucfirst(\str_replace('_', ' ', \preg_replace('/^[0-9]+_/', '', $file->filename)));
        }

        $information = [
            'file'       => $fileName,
            'mtime'      => $file->mtime,
            'alt'        => $altText,
            'caption'    => (!empty($meta['caption']) ? $meta['caption'] : ''),
            'title'      => $title,
            'metafile'   => $meta,
            'icon'       => 'assets/contao/images/' . $file->icon,
            'extension'  => $file->extension,
            'size'       => $file->filesize,
            'sizetext'   => \sprintf('(%s)', Controller::getReadableSize($file->filesize, 2)),
            'url'        => StringUtil::specialchars($this->getDownloadLink($fileName)),
            'isGdImage'  => false,
            'isSvgImage' => false,
            'isPicture'  => false,
        ];

        // Prepare GD images.
        if ($information['isGdImage'] = $file->isGdImage) {
            $information['src'] = \urldecode($this->resizeImage($fileName));
            $information['lb']  = 'lb_' . $this->getLightboxId();
            if (file_exists(TL_ROOT . '/' . $information['src'])) {
                $size              = getimagesize(TL_ROOT . '/' . $information['src']);
                $information['w']  = $size[0];
                $information['h']  = $size[1];
                $information['wh'] = $size[3];
            }
            $information['imageUrl'] = $fileName;
        }

        // Prepare SVG images.
        if ($information['isSvgImage'] = $file->isSvgImage) {
            $information['src']      = $fileName;
            $information['imageUrl'] = $fileName;
        }

        // Prepare the picture for provide the image size.
        if ($file->isImage && ($information['isPicture'] = (int) ($this->resizeImages[2] ?? 0))) {
            $projectDir = $this->rootDir;
            $staticUrl  = $this->filesContext->getStaticUrl();
            $picture    = $this->pictureFactory->create($projectDir . '/' . $file->path, $this->getResizeImages());

            $information['picture'] = [
                'alt'     => $altText,
                'title'   => $title,
                'img'     => $picture->getImg($projectDir, $staticUrl),
                'sources' => $picture->getSources($projectDir, $staticUrl)
            ];

            $information['imageUrl'] = $fileName;

            if (isset($GLOBALS['objPage']->layoutId)) {
                $lightboxSize                   = StringUtil::deserialize(
                    (LayoutModel::findByPk($GLOBALS['objPage']->layoutId)->lightboxSize ?? null),
                    true
                );
                $lightboxPicture                =
                    $this->pictureFactory->create($projectDir . '/' . $file->path, $lightboxSize);
                $information['lightboxPicture'] = $lightboxPicture;
                $information['imageUrl']        = $lightboxPicture->getImg($projectDir, $staticUrl)['src'];
            }
        }

        $this->modifiedTime[] = $file->mtime;
        $this->outputBuffer[] = $information;
    }

    /**
     * Resize the image if needed.
     *
     * @param string $fileName The file to resize.
     *
     * @return null|string
     */
    private function resizeImage($fileName)
    {
        [$width, $height, $mode] = $this->getResizeImages() + [null, null, null];
        if ($this->getShowImages() && ($width || $height || $mode)) {
            if ($this->imageFactory) {
                $image = $this->imageFactory->create(
                    $this->rootDir . '/' . $fileName,
                    [$width, $height, $mode]
                );

                return $image->getUrl($this->rootDir);
            }

            $event = new ResizeImageEvent($fileName, $width, $height, $mode);
            $this->dispatcher->dispatch($event, ContaoEvents::IMAGE_RESIZE);
            return $event->getResultImage();
        }

        return $fileName;
    }
}
