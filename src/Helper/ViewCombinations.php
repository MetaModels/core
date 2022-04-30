<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\System;
use Contao\User;
use Doctrine\DBAL\Connection;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\InputScreen\InputScreen;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Class ViewCombinations.
 *
 * Retrieve combinations of view and input screens for the currently logged in user (either frontend or backend).
 *
 * @deprecated This will get removed.
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
    protected $information = [];

    /**
     * All MetaModel combinations for lookup.
     *
     * Mapping is: id => table name.
     *
     * @var string[]
     */
    protected $tableMap = [];

    /**
     * MetaModels by their parent table.
     *
     * @var array
     */
    protected $parentMap = [];

    /**
     * MetaModels to their parent table.
     *
     * @var array
     */
    protected $childMap = [];

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $container;

    /**
     * The Contao user.
     *
     * @var User
     */
    private $user;

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $container  The service container.
     * @param User                        $user       The current user.
     * @param Connection|null             $connection Database connection.
     */
    public function __construct(IMetaModelsServiceContainer $container, User $user, Connection $connection = null)
    {
        $this->container = $container;
        $this->user      = $user;

        if (!$this->loadFromCache()) {
            $this->resolve();
            $this->saveToCache();
        }

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
        }

        $this->connection = $connection;
    }

    /**
     * Try to load the combinations from cache.
     *
     * @return string|null
     */
    protected function calculateCacheKey()
    {
        $key = 'view_combination_' . strtolower(TL_MODE);

        // Authenticate the user - if this fails, we use an anonymous cache file.
        if ($this->authenticateUser()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $key .= '_' . $this->getUser()->id;
        } else {
            $key .= '_anonymous';
        }

        return $key;
    }

    /**
     * Retrieve the current user.
     *
     * @return User
     */
    public function getUser()
    {
        static $authenticated;
        if (!isset($authenticated)) {
            $authenticated = true;
            $this->authenticateUser();
        }

        return $this->user;
    }

    /**
     * Try to load the combinations from cache.
     *
     * @return bool
     */
    protected function loadFromCache()
    {
        $key = $this->calculateCacheKey();
        if (!$this->container->getCache()->contains($key)) {
            return false;
        }

        // Perform loading now.
        $data = json_decode($this->container->getCache()->fetch($key), true);

        if (empty($data)) {
            return false;
        }

        $this->information = $data['information'];
        $this->tableMap    = $data['tableMap'];
        $this->parentMap   = $data['parentMap'];
        $this->childMap    = $data['childMap'];

        return true;
    }

    /**
     * Try to load the combinations from cache.
     *
     * @return bool
     */
    protected function saveToCache()
    {
        // Pretty print only came available with php 5.4.
        $flags = 0;
        if (defined('JSON_PRETTY_PRINT')) {
            $flags = JSON_PRETTY_PRINT;
        }

        return $this->container->getCache()->save(
            $this->calculateCacheKey(),
            json_encode(
                array(
                    'information' => $this->information,
                    'tableMap'    => $this->tableMap,
                    'parentMap'   => $this->parentMap,
                    'childMap'    => $this->childMap
                ),
                $flags
            )
        );
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

        $found = $this->getPaletteCombinationRows();

        if (!$found) {
            $found = array();
        }

        // Clean any undefined.
        foreach (array_keys($this->information) as $tableName) {
            if (empty($this->information[$tableName][self::COMBINATION])
                || empty($this->information[$tableName][self::COMBINATION]['dca_id'])
                || empty($this->information[$tableName][self::COMBINATION]['view_id'])
            ) {
                unset($this->information[$tableName]);
            }
        }

        $this->fetchInputScreenDetails();
    }

    /**
     * Set a table mapping.
     *
     * @param string $modelId   The id of the MetaModel.
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
     * @return array|\stdClass[]
     */
    protected function getCombinationsFromDatabase()
    {
        $groups = $this->getUserGroups();
        if (!$groups) {
            return null;
        }

        $statement = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_dca_combine', 't')
            ->where('t.' . strtolower(TL_MODE) . '_group IN (:groups)')
            ->orderBy('t.pid')
            ->addOrderBy('t.sorting', 'ASC')
            ->setParameter('groups', $groups)
            ->execute();

        return $statement->fetchAll(\PDO::FETCH_OBJ);
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

        foreach ($combinations as $combination) {
            /** @noinspection PhpUndefinedFieldInspection */
            $modelId   = $combination->pid;
            $modelName = $this->tableNameFromId($modelId);

            // Already a combination present, continue with next one.
            if (!empty($this->information[$modelName][self::COMBINATION])) {
                continue;
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $this->information[$modelName][self::MODELID] = $modelId;

            /** @noinspection PhpUndefinedFieldInspection */
            $this->information[$modelName][self::COMBINATION] = array(
                'dca_id'  => $combination->dca_id,
                'view_id' => $combination->view_id
            );

            $this->setTableMapping($modelId, $modelName);

            $success[] = $modelId;
        }

        return $success;
    }


    /**
     * Pull in all DCA settings for the buffered MetaModels and buffer them in the static class.
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException When a database error occurs.
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

        $statement = $this->connection
            ->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_dca', 't')
            ->where('t.id IN (:ids)')
            ->setParameter('id', $inputScreenIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        while ($inputScreens = $statement->fetch(\PDO::FETCH_OBJ)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $screenId = $inputScreens->id;
            /** @noinspection PhpUndefinedFieldInspection */
            $metaModelId   = $inputScreens->pid;
            $metaModelName = $this->tableNameFromId($metaModelId);

            $propertyRows = $this->connection
                ->createQueryBuilder()
                ->select('t.*')
                ->from('tl_metamodel_dcasetting', 't')
                ->where('t.pid=:id')
                ->andWhere('t.published=1')
                ->setParameter('id', $screenId)
                ->orderBy('t.sorting', 'ASC')
                ->execute();

            $conditions = $this->connection
                ->createQueryBuilder()
                ->select('cond.*, setting.attr_id AS setting_attr_id')
                ->from('tl_metamodel_dcasetting_condition', 'cond')
                ->leftJoin('cond', 'tl_metamodel_dcasetti', 'setting', 'cond.settingId=setting.id')
                ->leftJoin('setting', 'tl_metamodel_dca', 'dca', 'setting.pid=dca.id')
                ->where('dca.id=:id')
                ->andWhere('setting.published=1')
                ->andWhere('cond.enabled=1')
                ->setParameter('id', $screenId)
                ->orderBy('cond.sorting', 'ASC')
                ->execute();

            $groupSort = $this->connection
                ->createQueryBuilder()
                ->select('t.*')
                ->from('tl_metamodel_dca_sortgroup', 't')
                ->where('t.pid=:pid')
                ->setParameter('pid', $screenId)
                ->orderBy('t.sorting', 'ASC')
                ->execute();

            $inputScreen = array(
                'row'        => $inputScreens->row(),
                'properties' => $propertyRows->fetchAll(\PDO::FETCH_ASSOC),
                'conditions' => $conditions->fetchAll(\PDO::FETCH_ASSOC),
                'groupSort'  => $groupSort->fetchAll(\PDO::FETCH_ASSOC)
            );

            $this->information[$metaModelName][self::INPUTSCREEN] = $inputScreen;
            $this->information[$metaModelName][self::MODELID]     = $metaModelId;

            $parentTable = $inputScreen['row']['ptable'];
            if ($parentTable && !$this->isInputScreenStandalone($metaModelName)) {
                $this->parentMap[$parentTable][] = $this->information[$metaModelName][self::MODELID];
                $this->childMap[$metaModelName]  = $parentTable;
            }
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
                $inputScreen['conditions'],
                $inputScreen['groupSort']
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

        return $this->information[$metaModelName][self::COMBINATION]['view_id'] ?? null;
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
        $result = [];
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

        return $this->childMap[$metaModelName] ?? null;
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
                return [];
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
