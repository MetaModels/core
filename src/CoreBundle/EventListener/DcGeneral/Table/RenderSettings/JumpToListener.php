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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class JumpToListener extends AbstractAbstainingListener
{
    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The connection.
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
     * @param IFactory                 $factory           The factory.
     * @param Connection               $connection        The database connection.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator);
        $this->factory    = $factory;
        $this->connection = $connection;
        $this->translator = $translator;
    }

    /**
     * Translates the values of the jumpTo entries into the real array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'jumpTo')) {
            return;
        }

        $propInfo = $event->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperty('jumpTo');
        $value    = StringUtil::deserialize($event->getValue(), true);
        $extra    = $propInfo->getExtra();

        $newValues = [];
        $languages = $extra['columnFields']['langcode']['options'] ?? [];
        foreach (array_keys($languages) as $key) {
            $newValue = '';
            $filter   = 0;
            if ($value) {
                foreach ($value as $arr) {
                    if (!is_array($arr)) {
                        break;
                    }

                    // Set the new value and exit the loop.
                    if (array_search($key, $arr) !== false) {
                        $newValue = '{{link_url::' . $arr['value'] . '}}';
                        $filter   = $arr['filter'];
                        break;
                    }
                }
            }

            // Build the new array.
            $newValues[] = [
                'langcode' => $key,
                'value'    => $newValue,
                'filter'   => $filter
            ];
        }

        $event->setValue($newValues);
    }

    /**
     * Translates the values of the jumpTo entries into the internal array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty() !== 'jumpTo')) {
            return;
        }

        $value = StringUtil::deserialize($event->getValue(), true);

        foreach ($value as $k => $v) {
            $value[$k]['value'] = str_replace(['{{link_url::', '}}'], ['', ''], $v['value']);
        }

        $event->setValue(serialize($value));
    }

    /**
     * Provide options for template selection.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function buildWidget(BuildWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty()->getName() !== 'jumpTo')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel =
            $this->factory->getMetaModel($this->factory->translateIdToMetaModelName($model->getProperty('pid')));

        $extra = $event->getProperty()->getExtra();

        if ($metaModel->isTranslated()) {
            $arrLanguages = [];
            foreach ((array) $metaModel->getAvailableLanguages() as $strLangCode) {
                $arrLanguages[$strLangCode] = $this->translator
                    ->trans('LNG.'. $strLangCode, [], 'contao_languages');
            }
            asort($arrLanguages);

            $extra['minCount'] = count($arrLanguages);
            $extra['maxCount'] = count($arrLanguages);

            $extra['columnFields']['langcode']['options'] = $arrLanguages;
        } else {
            $extra['minCount'] = 1;
            $extra['maxCount'] = 1;

            $extra['columnFields']['langcode']['options'] = [
                'xx' => $this->translator
                    ->trans(
                        'tl_metamodel_rendersettings.jumpTo_allLanguages',
                        [],
                        'contao_tl_metamodel_rendersettings'
                    )
            ];
        }

        $extra['columnFields']['filter']['options'] = $this->getFilterSettings($model);

        $event->getProperty()->setExtra($extra);
    }


    /**
     * Retrieve the model filters for the MCW.
     *
     * @param ModelInterface $model The model containing the currently edited render setting.
     *
     * @return array
     */
    private function getFilterSettings(ModelInterface $model)
    {
        $filters = $this->connection
            ->createQueryBuilder()
            ->select('t.id', 't.name')
            ->from('tl_metamodel_filter', 't')
            ->where('t.pid=:id')
            ->setParameter('id', $model->getProperty('pid'))
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($filters as $filter) {
            $result[$filter['id']] = $filter['name'];
        }

        return $result;
    }
}
