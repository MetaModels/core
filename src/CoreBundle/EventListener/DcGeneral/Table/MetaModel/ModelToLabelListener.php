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
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class ModelToLabelListener extends AbstractAbstainingListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param Connection               $connection        The database connection.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        Connection $connection,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator);

        $this->connection = $connection;
        $this->translator = $translator;
    }

    /**
     * Render the html for the input screen condition.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function handle(ModelToLabelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $model     = $event->getModel();
        $tableName = $model->getProperty('tableName');

        if (!($model && !empty($tableName) && $this->connection->getSchemaManager()->tablesExist([$tableName]))) {
            return;
        }

        // Keep the previous label.
        $label = vsprintf($event->getLabel(), $event->getArgs());
        $image = ((bool) $model->getProperty('translated')) ? 'locale.png' : 'locale_1.png';
        $count = $this->connection
            ->createQueryBuilder()
            ->select('COUNT(t.id) AS itemCount')
            ->from($tableName, 't')
            ->execute()
            ->fetchColumn();

        switch ($count) {
            case 0:
                $transId = 'tl_metamodel.itemFormatCount.0';
                break;
            case 1:
                $transId = 'tl_metamodel.itemFormatCount.1';
                break;
            default:
                $transId = 'tl_metamodel.itemFormatCount.2:';
        }

        $event
            ->setLabel('
    <span class="name">
      <img src="bundles/metamodelscore/images/icons/%1$s" /> %2$s
      <span style="color:#b3b3b3; padding-left:3px">(%3$s)</span>
      <span style="color:#b3b3b3; padding-left:3px">[%4$s]</span>
    </span>')
            ->setArgs([
                $image,
                $label,
                $tableName,
                $this->translator->trans($transId, [$count], 'contao_tl_metamodel')
            ]);
    }
}
