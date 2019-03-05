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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\DcGeneral\PropertyConditionFactory;
use MetaModels\IFactory;

/**
 * This provides a way to obtain a MetaModel.
 */
abstract class AbstractConditionFactoryUsingListener extends AbstractListener
{
    /**
     * The property condition factory.
     *
     * @var PropertyConditionFactory
     */
    protected $conditionFactory;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param PropertyConditionFactory $conditionFactory  The condition factory.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        PropertyConditionFactory $conditionFactory
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
        $this->conditionFactory = $conditionFactory;
    }
}
