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
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Helper;

use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\InputScreen\InputScreen;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged in user (either frontend or backend).
 *
 * @package MetaModels
 */
abstract class ViewCombinations
{
    const COMBINATION   = 'combination';
    const INPUTSCREEN   = 'inputscreen';
    const RENDERSETTING = 'rendersetting';
    const MODELID       = 'metamodel';

    /**
     * All MetaModel combinations for lookup.
     *
     * @var array
     */
    protected $information = array();

    /**
     * All MetaModel combinations for lookup.
     *
     * Mapping is: id => table name.
     *
     * @var string[]
     */
    protected $tableMap = array();

    /**
     * MetaModels by their parent table.
     *
     * @var array
     */
    protected $parentMap = array();

    /**
     * MetaModels to their parent table.
     *
     * @var array
     */
    protected $childMap = array();

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $container;

    /**
     * The Contao user.
     *
     * @var \User
     */
    protected $user;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $container The service container.
     *
     * @param \User                       $user      The current user.
     *
     * @param string                      $cacheDir  The cache directory.
     */
    public function __construct(IMetaModelsServiceContainer $container, \User $user, $cacheDir = '')
    {
        $this->container = $container;
        $this->user      = $user;

        // FIXME: disabled for the moment.
        $cacheDir = null;

        if (!$this->loadFromCache($cacheDir)) {
            // Authenticate the user - if this fails, we stop everything.
            if (!$this->authenticateUser()) {
                return;
            }

            $this->resolve();
            $this->saveToCache($cacheDir);
        }
    }

    /**
     * Try to load the combinations from cache.
     *
     * @param string $cacheDir The cache directory.
     *
     * @return string|null
     */
    protected function calculateCacheFileName($cacheDir)
    {
        if (!$cacheDir) {
            return null;
        }

        $key = 'metamodels_user_information_' . md5(get_class($this->user));
        // Determine file key.

        /** @noinspection PhpUndefinedFieldInspection */
        if ($this->user->id) {
            /** @noinspection PhpUndefinedFieldInspection */
            $key .= '_' . $this->user->id;
        } else {
            $key .= '_anonymous';
        }

        return $cacheDir . '/' . $key . '.json';
    }

    /**
     * Try to load the combinations from cache.
     *
     * @param string $cacheDir The cache directory.
     *
     * @return bool
     */
    protected function loadFromCache($cacheDir)
    {
        $fileName = $this->calculateCacheFileName($cacheDir);

        if (!($fileName && file_exists($fileName))) {
            return false;
        }

        // Perform loading now.
        $this->information = json_decode(file_get_contents($fileName));

        return true;
    }

    /**
     * Try to load the combinations from cache.
     *
     * @param string $cacheDir The cache directory.
     *
     * @return bool
     */
    protected function saveToCache($cacheDir)
    {
        $fileName = $this->calculateCacheFileName($cacheDir);

        if (!($fileName && is_dir(dirname($fileName)))) {
            return false;
        }

        // Perform saving now.
        file_put_contents($fileName, json_encode($this->information, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Translate the MetaModel id to a valid table name.
     *
     * @param int $metaModelId The id of the MetaModel to translate.
     *
     * @return string
     */
    protected function tableNameFromId($metaModelId)
    {
        return $this->container->getFactory()->translateIdToMetaModelName($metaModelId);
    }

    /**
     * Resolve all combinations available.
     *
     * @return void
     */
    protected function resolve()
    {
        $factory = $this->container->getFactory();

        $names = $factory->collectNames();

        foreach ($names as $name) {
            $this->information[$name] = array
            (
                self::COMBINATION     => null,
                self::INPUTSCREEN     => null,
                self::RENDERSETTING   => null,
                self::MODELID         => null,
            );
        }

        $fallback = $this->getPaletteCombinationRows();

        if (!$fallback) {
            $fallback = array_keys($this->information);
        }

        if ($fallback) {
            $this->getPaletteCombinationDefault($fallback);
        }

        // Clean any undefined.
        foreach (array_keys($this->information) as $tableName) {
            if (empty($this->information[$tableName][self::COMBINATION])) {
                unset($this->information[$tableName]);
            }
        }

        $this->fetchInputScreenDetails();
    }

    /**
     * Set a table mapping.
     *
     * @param string $modelId   The id of the MetaModel.
     *
     * @param string $tableName The name of the MetaModel.
     *
     * @return void
     */
    protected function setTableMapping($modelId, $tableName)
    {
        $this->information[$tableName][self::MODELID] = $modelId;

        $this->tableMap[$modelId] = $tableName;
    }

    /**
     * Ensure the value is a MetaModel name and not an id.
     *
     * @param string $nameOrId The value to transform to the name.
     *
     * @return string
     */
    protected function getMetaModelName($nameOrId)
    {
        return isset($this->tableMap[$nameOrId]) ? $this->tableMap[$nameOrId] : $nameOrId;
    }

    /**
     * Retrieve the Contao database singleton instance.
     *
     * @return \Database
     */
    protected function getDatabase()
    {
        return $this->container->getDatabase();
    }

    /**
     * Authenticate the user preserving the object stack.
     *
     * NOTE: ONLY call this when not using the cache.
     *
     * @return bool
     */
    abstract protected function authenticateUser();

    /**
     * Retrieve the user groups of the current user.
     *
     * @return array
     */
    protected function getUserGroups()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->user->groups ? array_filter($this->user->groups) : array();
    }

    /**
     * Retrieve the palette combinations from the database.
     *
     * @return null|\Database\Result
     */
    protected function getCombinationsFromDatabase()
    {
        $groups = $this->getUserGroups();
        if (!$groups) {
            return null;
        }

        return $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_dca_combine WHERE %1$s IN (%2$s) OR %1$s=0 ORDER BY pid,sorting ASC',
                strtolower(TL_MODE) . '_group',
                implode(',', array_fill(0, count($groups), '?'))
            ))
            ->execute($groups);
    }

    /**
     * Fetch the palette view configurations valid for the given group ids.
     *
     * This returns the ids that have been resolved.
     *
     * @return int[]
     */
    protected function getPaletteCombinationRows()
    {
        $combinations = $this->getCombinationsFromDatabase();
        $success      = array();

        // No combinations present, nothing to resolve.
        if (!$combinations) {
            return array_keys($this->information);
        }

        while ($combinations->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $modelId   = $combinations->pid;
            $modelName = $this->tableNameFromId($modelId);

            // Already a combination present, continue with next one.
            if (!empty($this->information[$modelName][self::COMBINATION])) {
                continue;
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $this->information[$modelName][self::MODELID] = $modelId;

            /** @noinspection PhpUndefinedFieldInspection */
            $this->information[$modelName][self::COMBINATION] = array(
                'dca_id'  => $combinations->dca_id,
                'view_id' => $combinations->view_id
            );

            $this->setTableMapping($modelId, $modelName);

            $success[] = $modelId;
        }

        return $success;
    }

    /**
     * Get the default input screens (if any has been defined).
     *
     * @param int[] $metaModelIds The MetaModels for which input screens shall NOT be retrieved.
     *
     * @return void
     */
    protected function getDefaultInputScreens($metaModelIds)
    {
        $inputScreen = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_dca WHERE pid NOT IN (%s) AND isdefault=1',
                implode(',', $metaModelIds)
            ))
            ->execute();

        while ($inputScreen->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $modelId = $inputScreen->pid;

            if (!isset($this->tableMap[$modelId])) {
                $this->setTableMapping($modelId, $this->tableNameFromId($modelId));
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $this->information[$modelId][self::COMBINATION]['dca_id'] = $inputScreen->id;
        }
    }

    /**
     * Get the default render settings (if any has been defined).
     *
     * @param int[] $metaModelIds The MetaModels for which render settings shall NOT be retrieved.
     *
     * @return void
     */
    protected function getDefaultRenderSettings($metaModelIds)
    {
        $renderSetting = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_rendersettings WHERE pid NOT IN (%s) AND isdefault=1',
                implode(',', $metaModelIds)
            ))
            ->execute();

        while ($renderSetting->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $modelId = $renderSetting->pid;

            if (!isset($this->tableMap[$modelId])) {
                $this->setTableMapping($modelId, $this->tableNameFromId($modelId));
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $this->information[$modelId][self::COMBINATION]['view_id'] = $renderSetting->id;
        }
    }

    /**
     * Get the default combination of palette and view (if any has been defined).
     *
     * @param int[] $metaModelIds The MetaModels for which combinations shall NOT be retrieved.
     *
     * @return void
     */
    protected function getPaletteCombinationDefault($metaModelIds)
    {
        $this->getDefaultInputScreens($metaModelIds);
        $this->getDefaultRenderSettings($metaModelIds);
    }

    /**
     * Pull in all DCA settings for the buffered MetaModels and buffer them in the static class.
     *
     * @return void
     */
    protected function fetchInputScreenDetails()
    {
        $inputScreenIds = array();
        foreach ($this->information as $info) {
            $inputScreenIds[] = $info[self::COMBINATION]['dca_id'];
        }

        if (!$inputScreenIds) {
            return;
        }

        $inputScreens = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_dca WHERE id IN (%s)',
                implode(',', $inputScreenIds)
            ))
            ->execute();

        /** @noinspection PhpUndefinedFieldInspection */
        if (!$inputScreens->numRows) {
            return;
        }

        while ($inputScreens->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $screenId = $inputScreens->id;
            /** @noinspection PhpUndefinedFieldInspection */
            $metaModelId   = $inputScreens->pid;
            $metaModelName = $this->tableNameFromId($metaModelId);
            $propertyRows  = $this->getDatabase()
                ->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND published=1 ORDER BY sorting ASC')
                ->execute($screenId);

            $conditions = $this->getDatabase()
                ->prepare('
                    SELECT cond.*, setting.attr_id AS setting_attr_id
                    FROM tl_metamodel_dcasetting_condition AS cond
                    LEFT JOIN tl_metamodel_dcasetting AS setting
                    ON (cond.settingId=setting.id)
                    LEFT JOIN tl_metamodel_dca AS dca
                    ON (setting.pid=dca.id)
                    WHERE dca.id=? AND setting.published=1 AND cond.enabled=1
                    ORDER BY sorting ASC
                ')
                ->execute($screenId);

            $inputScreen = $this->information[$metaModelName][self::INPUTSCREEN] = array(
                'row'        => $inputScreens->row(),
                'properties' => $propertyRows->fetchAllAssoc(),
                'conditions' => $conditions->fetchAllAssoc()
            );

            $this->information[$metaModelName][self::MODELID] = $metaModelId;

            $parentTable = $inputScreen['row']['ptable'];
            if ($parentTable) {
                $this->parentMap[$parentTable][] = $this->information[$metaModelName][self::MODELID];
                $this->childMap[$metaModelName]  = $parentTable;
            }

            $this->information[$metaModelName][self::INPUTSCREEN] = $inputScreen;
        }
    }

    /**
     * Build an input screen instance and return it.
     *
     * @param string $metaModel The name or id of the MetaModel.
     *
     * @return IInputScreen
     */
    protected function buildInputScreen($metaModel)
    {
        $metaModelName = $this->getMetaModelName($metaModel);
        $inputScreen   = $this->information[$metaModelName][self::INPUTSCREEN];

        if (!is_object($inputScreen)) {
            $inputScreen = $this->information[$metaModelName][self::INPUTSCREEN] = new InputScreen(
                $this->container,
                $inputScreen['row'],
                $inputScreen['properties'],
                $inputScreen['conditions']
            );
        }

        return $inputScreen;
    }

    /**
     * Check if the input screen for a MetaModel is stand alone.
     *
     * @param string $metaModel The name of the MetaModel.
     *
     * @return bool
     */
    protected function isInputScreenStandalone($metaModel)
    {
        $information = $this->information[$metaModel];
        $inputScreen = isset($information[self::INPUTSCREEN]) ? $information[self::INPUTSCREEN] : null;
        if (!is_object($inputScreen)) {
            return ($inputScreen['row']['rendertype'] == 'standalone');
        }

        /** @var IInputScreen $inputScreen */
        return $inputScreen->isStandalone();
    }

    /**
     * Retrieve the render setting that is active for the current user.
     *
     * @param string $metaModel The name or id of the MetaModel.
     *
     * @return int
     */
    public function getRenderSetting($metaModel)
    {
        $metaModelName = $this->getMetaModelName($metaModel);

        return isset($this->information[$metaModelName][self::COMBINATION]['view_id'])
            ? $this->information[$metaModelName][self::COMBINATION]['view_id']
            : null;
    }

    /**
     * Retrieve the input screen that is active for the current user.
     *
     * @param string $metaModel The name or id of the MetaModel.
     *
     * @return int
     */
    public function getInputScreen($metaModel)
    {
        $inputScreen = $this->getInputScreenDetails($metaModel);
        return $inputScreen ? $inputScreen->getId() : null;
    }

    /**
     * Retrieve the input screen that is active for the current user.
     *
     * @param string $metaModel The name or id of the MetaModel.
     *
     * @return IInputScreen
     */
    public function getInputScreenDetails($metaModel)
    {
        return $this->buildInputScreen($this->getMetaModelName($metaModel));
    }

    /**
     * Retrieve all standalone input screens.
     *
     * @return IInputScreen[]
     */
    public function getStandaloneInputScreens()
    {
        $result = array();
        foreach (array_keys($this->information) as $modelName) {
            if ($this->isInputScreenStandalone($modelName)) {
                $result[] = $this->getInputScreenDetails($modelName);
            }
        }

        return $result;
    }

    /**
     * Get the name of the parenting MetaModel.
     *
     * @param string $metaModel The name of a child.
     *
     * @return string
     */
    public function getParentOf($metaModel)
    {
        $metaModelName = $this->getMetaModelName($metaModel);

        return isset($this->childMap[$metaModelName]) ? $this->childMap[$metaModelName] : null;
    }

    /**
     * Retrieve all standalone input screens or optionally the children of a given parent.
     *
     * @param string|null $parent The parent table for which the children shall be returned.
     *
     * @return string[]
     */
    public function getParentedInputScreenNames($parent = null)
    {
        $result = array();

        if ($parent) {
            if (!isset($this->parentMap[$parent])) {
                return array();
            }
            foreach ($this->parentMap[$parent] as $child) {
                $result[] = (isset($this->tableMap[$child])) ? $this->tableMap[$child] : $child;
            }

            return $result;
        }

        foreach (array_keys($this->information) as $modelName) {
            if (!$this->isInputScreenStandalone($modelName)) {
                $result[] = (isset($this->tableMap[$modelName])) ? $this->tableMap[$modelName] : $modelName;
            }
        }

        return $result;
    }

    /**
     * Retrieve all standalone input screens or optionally the children of a given parent.
     *
     * @param string|null $parent The parent table for which the children shall be returned.
     *
     * @return IInputScreen[]
     */
    public function getParentedInputScreens($parent = null)
    {
        $result = array();

        foreach ($this->getParentedInputScreenNames($parent) as $modelName) {
            $result[] = $this->getInputScreenDetails($modelName);
        }

        return $result;
    }
}
