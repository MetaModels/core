<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use MetaModels\IMetaModel;

/**
 * This provides a way to obtain a MetaModel.
 */
abstract class AbstractListener extends AbstractAbstainingListener
{
    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection
    ) {
        parent::__construct($scopeDeterminator);
        $this->factory    = $factory;
        $this->connection = $connection;
    }

    /**
     * Retrieve the MetaModel the given model is attached to.
     *
     * @param ModelInterface $model The input screen model for which to retrieve the MetaModel.
     *
     * @return IMetaModel
     *
     * @throws DcGeneralInvalidArgumentException When an invalid model has been passed or the model does not have an id.
     */
    protected function getMetaModelFromModel(ModelInterface $model)
    {
        if (!(($model->getProviderName() == 'tl_metamodel_dcasetting') && $model->getProperty('pid'))) {
            throw new DcGeneralInvalidArgumentException(
                sprintf(
                    'Model must originate from tl_metamodel_dcasetting and be saved, this one originates from %s and ' .
                    'has pid %s',
                    $model->getProviderName(),
                    $model->getProperty('pid')
                )
            );
        }

        $metaModelId = $this->connection->createQueryBuilder()
            ->select('pid')
            ->from('tl_metamodel_dca')
            ->where('id=:id')
            ->setParameter('id', $model->getProperty('pid'))
            ->execute()
            ->fetchColumn();

        $tableName = $this->factory->translateIdToMetaModelName($metaModelId);

        return $this->factory->getMetaModel($tableName);
    }
}
