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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ViewCombination;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;

/**
 * This class obtains information from the database about input screens.
 */
class InputScreenInformationBuilder
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     * @param IFactory   $factory    The MetaModels factory.
     */
    public function __construct(Connection $connection, IFactory $factory)
    {
        $this->connection = $connection;
        $this->factory    = $factory;
    }

    /**
     * Fetch information about an input screen.
     *
     * @param array $idList The ids of the input screens to obtain (table name => id).
     *
     * @return array
     */
    public function fetchInputScreens($idList)
    {
        $builder = $this->connection->createQueryBuilder();
        $screens = $builder
            ->select('*')
            ->from('tl_metamodel_dca')
            ->where($builder->expr()->in('id', ':idList'))
            ->setParameter('idList', $idList, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        $keys   = array_flip($idList);
        foreach ($screens as $screen) {
            $metaModelName = $keys[$screen['id']];
            $result[$metaModelName] = $this->prepareInputScreen($metaModelName, $screen);
        }

        return $result;
    }

    /**
     * Prepare the input screen data.
     *
     * @param string $modelName The MetaModel name.
     * @param array  $screen    The screen meta data.
     *
     * @return array
     */
    private function prepareInputScreen($modelName, $screen)
    {
        $metaModel   = $this->factory->getMetaModel($modelName);
        $caption     = ['' => $metaModel->getName()];
        $description = ['' => $metaModel->getName()];
        foreach (StringUtil::deserialize($screen['backendcaption'], true) as $languageEntry) {
            $langCode               = $languageEntry['langcode'];
            $caption[$langCode]     = !empty($label = $languageEntry['label']) ? $label : $caption[''];
            $description[$langCode] = !empty($title = $languageEntry['description']) ? $title : $description[''];
            if ($metaModel->getFallbackLanguage() === $langCode) {
                $caption['']     = $label;
                $description[''] = $title;
            }
        }

        return [
            'meta'        => $screen,
            'properties'  => $this->fetchPropertiesFor($screen['id']),
            'conditions'  => [],
            'groupSort'   => [],
            'label'       => $caption,
            'description' => $description
        ];
    }

    /**
     * Fetch all properties for the passed input screen.
     *
     * @param string $inputScreenId The input screen to obtain properties for.
     *
     * @return array
     */
    private function fetchPropertiesFor($inputScreenId)
    {
        $builder  = $this->connection->createQueryBuilder();
        return $builder
            ->select('*')
            ->from('tl_metamodel_dcasetting')
            ->where('pid=:pid')
            ->setParameter('pid', $inputScreenId)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
}
