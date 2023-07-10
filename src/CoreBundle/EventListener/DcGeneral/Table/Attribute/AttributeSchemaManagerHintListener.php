<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use Contao\Message;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class takes care of updating all data when an attribute has been saved.
 */
class AttributeSchemaManagerHintListener extends BaseListener
{
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
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IAttributeFactory $attributeFactory,
        IFactory $factory,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator, $attributeFactory, $factory);
        $this->translator = $translator;
    }

    /**
     * Add hint at attribute.
     *
     * @param PreEditModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PreEditModelEvent $event): void
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        Message::addInfo($this->translator->trans('hint_schema_manager', [], 'tl_metamodel_attribute'));
    }
}
