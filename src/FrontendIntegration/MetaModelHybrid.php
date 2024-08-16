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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\BackendTemplate;
use Contao\Database\Result;
use Contao\Hybrid;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsServiceContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base implementation of a MetaModel Hybrid element.
 *
 * @property string $id                       The id of the element.
 * @property string $name                     The module name to use (if type is module).
 * @property string $metamodel                The id of the MetaModel to use.
 * @property string $metamodel_filtering      The id of the MetaModel filter setting to use.
 * @property string $metamodel_rendersettings The id of the MetaModel render setting to use.
 * @property bool   $metamodel_sort_override  The flag to override sorting.
 *
 * @psalm-type TDatabaseResult=object{
 *   cssID: string,
 *   typePrefix: ?string,
 *   type: string,
 *   headline: string,
 * }
 *
 * @deprecated We switched to fragments in MetaModels 2.2. To be removed in MetaModels 3.0.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class MetaModelHybrid extends Hybrid
{
    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName;

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink;

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix;

    /**
     * The database connection.
     *
     * @var Connection|null
     */
    private ?Connection $connection = null;

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated The service container will get removed, inject needed services instead.
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function getServiceContainer()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        /** @psalm-suppress DeprecatedClass */
        $serviceContainer = System::getContainer()->get(MetaModelsServiceContainer::class);
        assert($serviceContainer instanceof MetaModelsServiceContainer);

        return $serviceContainer;
    }

    /**
     * Retrieve the factory.
     *
     * @return IFactory
     */
    protected function getFactory()
    {
        $factory = System::getContainer()->get('metamodels.factory');
        assert($factory instanceof IFactory);

        return $factory;
    }

    /**
     * Retrieve the connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        if (null === $this->connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
            assert($connection instanceof Connection);
            $this->connection = $connection;
        }

        return $this->connection;
    }

    /**
     * Create a new instance.
     *
     * @param Result|TDatabaseResult $objElement The object from the database.
     * @param string                             $strColumn  The column the element is displayed within.
     */
    public function __construct($objElement, $strColumn = 'main')
    {
        /** @psalm-suppress ArgumentTypeCoercion - Contao has incomplete type annotation. */
        parent::__construct($objElement, $strColumn);

        $this->arrData = \method_exists($objElement, 'row') ? $objElement->row() : (array) $objElement;

        // Get CSS ID and headline from the parent element (!).
        /** @psalm-suppress UndefinedThisPropertyFetch */
        $this->cssID      = StringUtil::deserialize($objElement->cssID, true);
        $this->typePrefix = $objElement->typePrefix ?? '';
        /** @psalm-suppress UndefinedThisPropertyFetch */
        $this->strKey = $objElement->type;
        $arrHeadline  = StringUtil::deserialize($objElement->headline);
        /** @psalm-suppress UndefinedThisPropertyFetch */
        $this->headline = \is_array($arrHeadline) ? $arrHeadline['value'] : $arrHeadline;
        $this->hl       = \is_array($arrHeadline) ? $arrHeadline['unit'] : 'h1';
    }

    /**
     * Generate the list.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @throws \Doctrine\DBAL\Exception
     */
    public function generate()
    {
        if (
            (bool) System::getContainer()->get('contao.routing.scope_matcher')
                ?->isBackendRequest(
                    System::getContainer()->get('request_stack')?->getCurrentRequest() ?? Request::create('')
                )
        ) {
            $strInfo = '';
            if ($this->metamodel) {
                // Add CSS file.
                $GLOBALS['TL_CSS'][] = '/bundles/metamodelscore/css/style.css';

                // Retrieve name of MetaModel.
                $infoTemplate =
                    '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="%s">%s:</abbr></span> %s</div>';

                $factory = $this->getFactory();
                $metaModelName = $factory->translateIdToMetaModelName($this->metamodel);
                $metaModel = $factory->getMetaModel($metaModelName);
                assert($metaModel instanceof IMetaModel);

                $translator = System::getContainer()->get('translator');
                assert($translator instanceof TranslatorInterface);

                $strInfo = \sprintf(
                    $infoTemplate,
                    $translator->trans('mm_be_info_name.description', [], 'metamodels_wildcard'),
                    $translator->trans('mm_be_info_name.label', [], 'metamodels_wildcard'),
                    $metaModel->getName()
                );

                $database = $this->getConnection();

                // Retrieve name of filter.
                if ($this->metamodel_filtering) {
                    $infoFi = $database
                        ->createQueryBuilder()
                        ->select('t.name')
                        ->from('tl_metamodel_filter', 't')
                        ->where('t.id=:id')
                        ->setParameter('id', $this->metamodel_filtering)
                        ->setMaxResults(1)
                        ->executeQuery()
                        ->fetchFirstColumn();

                    if ($infoFi) {
                        $strInfo .= \sprintf(
                            $infoTemplate,
                            $translator->trans('mm_be_info_filter.description', [], 'metamodels_wildcard'),
                            $translator->trans('mm_be_info_filter.label', [], 'metamodels_wildcard'),
                            \current($infoFi)
                        );
                    }
                }

                // Retrieve name of render setting.
                if ($this->metamodel_rendersettings) {
                    $infoRs = $database
                        ->createQueryBuilder()
                        ->select('t.name')
                        ->from('tl_metamodel_rendersettings', 't')
                        ->where('t.id=:id')
                        ->setParameter('id', $this->metamodel_rendersettings)
                        ->setMaxResults(1)
                        ->executeQuery()
                        ->fetchFirstColumn();

                    if ($infoRs) {
                        $strInfo .= \sprintf(
                            $infoTemplate,
                            $translator->trans('mm_be_info_render_setting.description', [], 'metamodels_wildcard'),
                            $translator->trans('mm_be_info_render_setting.label', [], 'metamodels_wildcard'),
                            \current($infoRs)
                        );
                    }
                }
            }

            $objTemplate = new BackendTemplate('be_wildcard');
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $objTemplate->wildcard = $this->wildCardName . $strInfo;
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $objTemplate->title = $this->headline;
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $objTemplate->id = $this->id;
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $objTemplate->link = ($this->typePrefix === 'mod_' ? 'FE-Modul: ' : '') . $this->name;
            $objTemplate->href = \sprintf($this->wildCardLink, $this->id);

            return $objTemplate->parse();
        }

        return parent::generate();
    }
}
