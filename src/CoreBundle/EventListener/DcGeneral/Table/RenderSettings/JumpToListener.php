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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use Contao\CoreBundle\Intl\Locales;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_keys;
use function count;
use function in_array;
use function is_array;
use function serialize;
use function str_replace;

/**
 * This handles the rendering of models to labels.
 */
class JumpToListener extends AbstractAbstainingListener
{
    /** @psalm-suppress MissingClassConstType */
    private const DEFAULT_TYPE = UrlGeneratorInterface::ABSOLUTE_PATH;

    /** @psalm-suppress MissingClassConstType */
    private const TYPE_MAP = [
        'absolute_url'  => UrlGeneratorInterface::ABSOLUTE_URL,
        'absolute_path' => UrlGeneratorInterface::ABSOLUTE_PATH,
        'relative_path' => UrlGeneratorInterface::RELATIVE_PATH,
        'network_path'  => UrlGeneratorInterface::NETWORK_PATH,
    ];

    /** @psalm-suppress MissingClassConstType */

    private const TYPE_MAP_INVERSE = [
        UrlGeneratorInterface::ABSOLUTE_URL  => 'absolute_url',
        UrlGeneratorInterface::ABSOLUTE_PATH => 'absolute_path',
        UrlGeneratorInterface::RELATIVE_PATH => 'relative_path',
        UrlGeneratorInterface::NETWORK_PATH  => 'network_path',
    ];

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

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
     * Input: Int for the type.
     * Output: String for the type
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

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $propInfo = $dataDefinition->getPropertiesDefinition()->getProperty('jumpTo');
        $value    = StringUtil::deserialize($event->getValue(), true);
        $extra    = $propInfo->getExtra();

        $newValues = [];
        /** @var array<string, mixed> $languages */
        $languages = $extra['columnFields']['langcode']['options'] ?? [];
        foreach (array_keys($languages) as $key) {
            $newValue = '';
            $filter   = 0;
            $type     = self::TYPE_MAP_INVERSE[self::DEFAULT_TYPE];
            if ($value) {
                foreach ($value as $arr) {
                    if (!is_array($arr)) {
                        break;
                    }

                    if (in_array($key, $arr, true)) {
                        $newValue = '{{link_url::' . $arr['value'] . '}}';
                        $filter   = $arr['filter'];

                        // Set the new value and exit the loop.
                        if (\in_array($key, $arr, true)) {
                            $newValue = '{{link_url::' . $arr['value'] . '}}';
                            $type     = self::TYPE_MAP_INVERSE[$arr['type'] ?? self::DEFAULT_TYPE];
                            $filter   = $arr['filter'];
                            break;
                        }
                        break;
                    }
                    // Set the new value and exit the loop.
                }
            }

            // Build the new array.
            $newValues[] = [
                'langcode' => $key,
                'type'     => $type,
                'value'    => $newValue,
                'filter'   => $filter
            ];
        }

        $event->setValue($newValues);
    }

    /**
     * Translates the values of the jumpTo entries into the internal array.
     * Input: String for the type
     * Output: Int for the type.
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
            $value[$k]['type']  = self::TYPE_MAP[$v['type']] ?? self::DEFAULT_TYPE;
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
        assert($metaModel instanceof IMetaModel);

        $extra = $event->getProperty()->getExtra();

        /** @psalm-suppress DeprecatedMethod */
        if ($metaModel->isTranslated()) {
            /** @psalm-suppress DeprecatedMethod */
            $fallback = $metaModel->getFallbackLanguage();

            $arrLanguages = [];
            $rowClasses   = [];
            $intlLocales  = System::getContainer()->get('contao.intl.locales');
            assert($intlLocales instanceof Locales);
            $labels = $intlLocales->getLocales();
            /** @psalm-suppress DeprecatedMethod */
            foreach ((array) $metaModel->getAvailableLanguages() as $strLangCode) {
                $arrLanguages[$strLangCode] = $labels[$strLangCode];
                $rowClasses[]               = ($strLangCode === $fallback) ? 'fallback_language' : 'normal_language';
            }

            $extra['minCount'] = count($arrLanguages);
            $extra['maxCount'] = count($arrLanguages);

            $extra['columnFields']['langcode']['options']            = $arrLanguages;
            $extra['columnFields']['langcode']['eval']['rowClasses'] = $rowClasses;
        } else {
            $extra['minCount'] = 1;
            $extra['maxCount'] = 1;

            $extra['columnFields']['langcode']['options'] = [
                'xx' => $this->translator->trans('jumpTo_allLanguages', [], 'tl_metamodel_rendersettings')
            ];
        }

        $extra['columnFields']['type']['options']   = $this->getUrlTypes();
        $extra['columnFields']['filter']['options'] = $this->getFilterSettings($model);

        $event->getProperty()->setExtra($extra);
    }


    private function getUrlTypes(): array
    {
        $result = [];
        foreach (self::TYPE_MAP_INVERSE as $typeName) {
            $result[$typeName] = $this->translator->trans(
                'jumpTo_type.' . $typeName,
                [],
                'tl_metamodel_rendersettings'
            );
        }

        return $result;
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
