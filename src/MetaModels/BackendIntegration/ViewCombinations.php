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

namespace MetaModels\BackendIntegration;

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
class ViewCombinations
{
    const COMBINATION   = 'combination';
    const INPUTSCREEN   = 'inputscreen';
    const RENDERSETTING = 'rendersetting';
    const MODELNAME     = 'metamodel';

    /**
     * All MetaModel combinations for lookup.
     *
     * @var array
     */
    protected $information = array();

    /**
     * All MetaModel combinations for lookup.
     *
     * @var array
     */
    protected $tableMap = array();

    /**
     * MetaModel by their parent table.
     *
     * @var array
     */
    protected $parentMap = array();

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
        if ($this->user->id) {
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
     * Resolve all combinations available.
     *
     * @return void
     */
    protected function resolve()
    {
        $factory = $this->container->getFactory();

        $names = $factory->collectNames();

        $metaModels = array();

        foreach ($names as $name) {
            $metaModel         = $factory->getMetaModel($name);
            $metaModels[$name] = $metaModel;

            $this->information[$metaModel->get('id')] = array
            (
                self::COMBINATION     => null,
                self::INPUTSCREEN     => null,
                self::RENDERSETTING   => null,
                self::MODELNAME       => $name,
            );

            $this->tableMap[$name] = $metaModel->get('id');
        }

        $fallback = $this->getPaletteCombinationRows();

        if (!$fallback) {
            $fallback = array_keys($this->information);
        }

        if ($fallback) {
            $this->getPaletteCombinationDefault($fallback);
        }

        // Clean any undefined.
        foreach (array_keys($this->information) as $id) {
            if (empty($this->information[$id][self::COMBINATION])) {
                unset($this->tableMap[$this->information[$id][self::MODELNAME]]);
                unset($this->information[$id][self::COMBINATION]);
            }
        }

        $this->fetchInputScreenDetails();
        $this->fetchRenderSettingDetails();
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
     * Retrieve the user groups of the current user.
     *
     * @return array
     */
    protected function getUserGroups()
    {
        // Try to get the group(s)
        // there might be a NULL in there as BE admins have no groups and user might have one but it is not mandatory.
        // I would prefer a default group for both, fe and be groups.
        $groups = $this->user->groups ? array_filter($this->user->groups) : array();

        // Special case in combinations, admins have the implicit group id -1.
        if (($this->user instanceof \BackendUser) && $this->user->admin) {
            $groups[] = -1;
        }

        return $groups;
    }

    /**
     * Fetch the palette view configurations valid for the given group ids.
     *
     * This returns the ids that have not been resolved as no combination has been defined and therefore the default
     * must be used for.
     *
     * @return int[]
     */
    protected function getPaletteCombinationRows()
    {
        $groups = $this->getUserGroups();
        if (!$groups) {
            return array();
        }

        $groupColumn     = ($this->user instanceof \BackendUser) ? 'be_group' : 'fe_group';
        $objCombinations = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_dca_combine WHERE %s IN (%s) ORDER BY pid,sorting ASC',
                $groupColumn,
                implode(',', $groups)
            ))
            ->execute();

        $success = array();

        while ($objCombinations->next()) {
            // Already a combination present, continue with next one.
            if (!empty($this->information[$objCombinations->pid][self::COMBINATION])) {
                continue;
            }
            $this->information[$objCombinations->pid][self::COMBINATION] = $objCombinations->row();

            $success[] = $objCombinations->pid;
        }

        return array_diff(array_keys($this->information), $success);
    }

    /**
     * Get the default combination of palette and view (if any has been defined).
     *
     * @param int[] $metaModelIds The MetaModels for which combinations shall be retrieved.
     *
     * @return void
     */
    protected function getPaletteCombinationDefault($metaModelIds)
    {
        $inputScreen = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_dca WHERE pid IN (%s) AND isdefault=1',
                implode(',', $metaModelIds)
            ))
            ->execute();

        while ($inputScreen->next()) {
            $this->information[$inputScreen->pid][self::COMBINATION]['dca_id'] = $inputScreen->id;
        }

        $renderSetting = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_rendersettings WHERE pid IN (%s) AND isdefault=1',
                implode(',', $metaModelIds)
            ))
            ->execute();

        while ($renderSetting->next()) {
            $this->information[$renderSetting->pid][self::COMBINATION]['view_id'] = $renderSetting->id;
        }
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
            if (!empty($info[self::COMBINATION]['dca_id'])) {
                $inputScreenIds[] = $info[self::COMBINATION]['dca_id'];
            }
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

        if (!$inputScreens->numRows) {
            return;
        }

        while ($inputScreens->next()) {
            $propertyRows = $this->getDatabase()
                ->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND published=1 ORDER BY sorting ASC')
                ->execute($inputScreens->id);

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
                ->execute($inputScreens->id);

            $inputScreen = $this->information[$inputScreens->pid][self::INPUTSCREEN] = new InputScreen(
                $this->container,
                $inputScreens->row(),
                $propertyRows->fetchAllAssoc(),
                $conditions->fetchAllAssoc()
            );

            $parentTable = $inputScreen->getParentTable();
            if ($parentTable) {
                $this->parentMap[$parentTable][] = $this->information[$inputScreens->pid][self::MODELNAME];
            }

            $this->information[$inputScreens->pid][self::INPUTSCREEN] = $inputScreen;
        }
    }

    /**
     * Pull in all DCA settings for the buffered MetaModels and buffer them in the static class.
     *
     * @return void
     */
    protected function fetchRenderSettingDetails()
    {
        $renderSettingIds = array();
        foreach ($this->information as $info) {
            if (!empty($info[self::COMBINATION]['view_id'])) {
                $renderSettingIds[] = $info[self::COMBINATION]['view_id'];
            }
        }

        if (!$renderSettingIds) {
            return;
        }

        $renderSettings = $this->getDatabase()
            ->prepare(sprintf(
                'SELECT * FROM tl_metamodel_rendersettings WHERE id IN (%s)',
                implode(',', $renderSettingIds)
            ))
            ->execute();

        while ($renderSettings->next()) {
            $this->information[$renderSettings->pid][self::RENDERSETTING] = $renderSettings->row();
        }
    }

    /**
     * Retrieve the render setting that is active for the current user.
     *
     * @param string|int $metaModel The name or id of the MetaModel.
     *
     * @return int
     */
    public function getRenderSetting($metaModel)
    {
        $renderSetting = $this->getRenderSettingDetails($metaModel);

        return $renderSetting ? $renderSetting['id'] : null;
    }

    /**
     * Retrieve the input screen that is active for the current user.
     *
     * @param string|int $metaModel The name or id of the MetaModel.
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
     * @param string|int $metaModel The name or id of the MetaModel.
     *
     * @return IInputScreen
     */
    public function getInputScreenDetails($metaModel)
    {
        if (!is_numeric($metaModel)) {
            $metaModel = $this->tableMap[$metaModel];
        }

        return $this->information[$metaModel][self::INPUTSCREEN];
    }

    /**
     * Retrieve the input screen that is active for the current user.
     *
     * @param string|int $metaModel The name or id of the MetaModel.
     *
     * @return array
     */
    public function getRenderSettingDetails($metaModel)
    {
        if (!is_numeric($metaModel)) {
            $metaModel = $this->tableMap[$metaModel];
        }

        return $this->information[$metaModel][self::RENDERSETTING];
    }

    /**
     * Retrieve all standalone input screens.
     *
     * @return IInputScreen[]
     */
    public function getStandaloneInputScreens()
    {
        $result = array();
        foreach ($this->information as $information) {
            /** @var IInputScreen $inputScreen */
            $inputScreen = isset($information[self::INPUTSCREEN]) ? $information[self::INPUTSCREEN] : null;
            if ($inputScreen && $inputScreen->isStandalone()) {
                $result[] = $information[self::INPUTSCREEN];
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

        if ($parent) {
            if (!isset($this->parentMap[$parent])) {
                return array();
            }
            foreach ($this->parentMap[$parent] as $child) {
                $result[] = $this->getInputScreenDetails($child);
            }

            return $result;
        }

        foreach ($this->information as $information) {
            /** @var IInputScreen $inputScreen */
            $inputScreen = isset($information[self::INPUTSCREEN]) ? $information[self::INPUTSCREEN] : null;
            if ($inputScreen && !$inputScreen->isStandalone()) {
                $result[] = $information[self::INPUTSCREEN];
            }
        }

        return $result;
    }
}
