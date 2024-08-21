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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Framework\Adapter;
use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\BackendIntegration\PurgeCache;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

/**
 * This controller provides the base for the add-all handlers for input screens and render settings.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractAddAllController extends AbstractBackendController
{
    /**
     * Adapter to the Contao\System class.
     *
     * @var Adapter<System>
     */
    private Adapter $systemAdapter;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The cache purger.
     *
     * @var PurgeCache
     */
    private PurgeCache $purger;

    /**
     * The list of known attributes.
     *
     * @var array
     */
    private array $knownAttributes;

    /**
     * The twig engine.
     *
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * Sorting start value.
     *
     * @var int
     */
    private int $startSort;

    /**
     * Create a new instance.
     *
     * @param TwigEnvironment     $twig          The templating instance.
     * @param TranslatorInterface $translator    The translator.
     * @param IFactory            $factory       The MetaModels factory.
     * @param Connection          $connection    The database connection.
     * @param Adapter<System>     $systemAdapter Adapter to the Contao\System class.
     * @param PurgeCache          $purger        The cache purger.
     */
    public function __construct(
        TwigEnvironment $twig,
        TranslatorInterface $translator,
        IFactory $factory,
        Connection $connection,
        Adapter $systemAdapter,
        PurgeCache $purger
    ) {
        $this->twig          = $twig;
        $this->translator    = $translator;
        $this->factory       = $factory;
        $this->connection    = $connection;
        $this->systemAdapter = $systemAdapter;
        $this->purger        = $purger;
    }

    /**
     * Create an empty data set for inclusion into the database.
     *
     * @param IAttribute $attribute The attribute to generate the data for.
     * @param string     $parentId  The parent id.
     * @param bool       $activate  Flag if the setting shall get activated.
     * @param int        $sort      The sorting value.
     * @param string     $tlclass   The CSS class.
     *
     * @return array
     */
    abstract protected function createEmptyDataFor(IAttribute $attribute, $parentId, $activate, $sort, $tlclass = '');

    /**
     * Test if the passed attribute is acceptable.
     *
     * @param IAttribute $attribute The attribute to check.
     *
     * @return bool
     */
    abstract protected function accepts(IAttribute $attribute);

    /**
     * Process the request.
     *
     * @param string  $table         The table name.
     * @param string  $metaModelName The MetaModel name.
     * @param string  $parentId      The parent id.
     * @param Request $request       The request.
     *
     * @return Response
     *
     * @throws \RuntimeException Throws if you could not retrieve a metamodel.
     */
    protected function process($table, $metaModelName, $parentId, Request $request)
    {
        $this->knownAttributes = $this->fetchExisting($table, $parentId);

        $metaModel = $this->factory->getMetaModel($metaModelName);
        if (!$metaModel) {
            throw new \RuntimeException('Could not retrieve MetaModel ' . $metaModelName);
        }
        if ($request->request->has('add') || $request->request->has('saveNclose')) {
            $this->perform($table, $request, $metaModel, $parentId);
            // If we want to close, go back to referer.
            if ($request->request->has('saveNclose')) {
                return new RedirectResponse($this->getReferer($request, $table, false));
            }
        }

        return $this->render('@MetaModelsCore/Backend/add-all.html.twig', $this->renderOutput($table, $metaModel, $request));

        return new Response(
            $this->twig->render(
                '@MetaModelsCore/Backend/add-all.html.twig',
                $this->renderOutput($table, $metaModel, $request)
            )
        );
    }

    /**
     * Render the output array for the template.
     *
     * @param string     $table     The name of the table to add to.
     * @param IMetaModel $metaModel The MetaModel name on which to work on.
     * @param Request    $request   The request.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function renderOutput($table, $metaModel, Request $request)
    {
        $fields   = $this->generateForm($table, $metaModel, $request);
        $headline = $this->translator->trans('addall.description', [], $table);

        $GLOBALS['TL_CSS']['metamodels.core'] = '/bundles/metamodelscore/css/style.css';

        System::loadLanguageFile('default');

        return [
            'title'         => $headline,
            'action'        => '',
            'requestToken'  => System::getContainer()->get('contao.csrf.token_manager')?->getDefaultTokenValue(),
            'href'          => $this->getReferer($request, $table, true),
            'backBt'        => $this->translator->trans('backBT', [], $table),
            'add'           => $this->translator->trans('continue', [], $table),
            'saveNclose'    => $this->translator->trans('saveNclose', [], $table),
            'activate'      => $this->translator->trans('addAll_activate', [], $table),
            'tlclass'       => '',
            'headline'      => $headline,
            'selectAll'     => $this->translator->trans('selectAll', [], $table) . '.',
            'cacheMessage'  => '',
            'updateMessage' => '',
            'hasCheckbox'   => \count($fields) > 0,
            'fields'        => $fields,
            'theme'         => 'flexible',
            //'stylesheets'   => ['/bundles/metamodelscore/css/style.css']
        ];
    }

    /**
     * Fetch existing entries.
     *
     * @param string $table    The table name to fetch from.
     * @param string $parentId The parent id.
     *
     * @return array
     */
    private function fetchExisting(string $table, string $parentId): array
    {
        // Keep the sorting value.
        $this->startSort       = 0;
        $this->knownAttributes = [];

        $alreadyExisting = $this->connection
            ->createQueryBuilder()
            ->select('t.*')
            ->from($table, 't')
            ->where('t.pid=:pid')
            ->setParameter('pid', $parentId)
            ->orderBy('t.sorting')
            ->executeQuery();

        foreach ($alreadyExisting->fetchAllAssociative() as $item) {
            $this->knownAttributes[$item['attr_id']] = $item;
            $this->startSort                         = $item['sorting'];
        }

        return $this->knownAttributes;
    }

    /**
     * Check if an attribute is already present.
     *
     * @param IAttribute $attribute The attribute to check.
     *
     * @return bool
     */
    private function knowsAttribute(IAttribute $attribute): bool
    {
        return \array_key_exists($attribute->get('id'), $this->knownAttributes);
    }

    /**
     * Generate the form.
     *
     * @param string     $table     The table name.
     * @param IMetaModel $metaModel The MetaModel name.
     * @param Request    $request   The request.
     *
     * @return array
     */
    private function generateForm(string $table, IMetaModel $metaModel, Request $request): array
    {
        $fields = [];
        // Loop over all attributes now.
        foreach ($metaModel->getAttributes() as $attribute) {
            $attrId = $attribute->get('id');
            if (!$this->accepts($attribute)) {
                continue;
            }
            if ($this->knowsAttribute($attribute)) {
                $fields[] = [
                    'checkbox' => false,
                    'text'     => $this->checkboxCaption('addAll_alreadycontained', $table, $attribute),
                    'class'    => 'tl_info',
                    'attr_id'  => $attrId,
                    'name'     => 'attribute_' . $attrId
                ];
                continue;
            } elseif ($this->isAttributeSubmitted($attrId, $request)) {
                $fields[] = [
                    'checkbox' => false,
                    'text'     => $this->checkboxCaption('addAll_addsuccess', $table, $attribute),
                    'class'    => 'tl_confirm',
                    'attr_id'  => $attrId,
                    'name'     => 'attribute_' . $attrId
                ];
                continue;
            }
            $fields[] = [
                'checkbox' => true,
                'text'     => $this->checkboxCaption('addAll_willadd', $table, $attribute),
                'class'    => 'tl_new',
                'attr_id'  => $attrId,
                'name'     => 'attribute_' . $attrId
            ];
        }

        return $fields;
    }

    /**
     * Translate the checkbox caption.
     *
     * @param string     $key       The language sub key.
     * @param string     $table     The table name.
     * @param IAttribute $attribute The attribute.
     *
     * @return string
     */
    private function checkboxCaption(string $key, string $table, IAttribute $attribute): string
    {
        return $this->translator->trans(
            $key,
            [
                '%name%'    => $attribute->getName(),
                '%type%'    => $attribute->get('type'),
                '%colName%' => $attribute->getColName()
            ],
            $table
        );
    }

    /**
     * Test if an attribute has been submitted.
     *
     * @param string  $attributeId The attribute id.
     * @param Request $request     The request.
     *
     * @return bool
     */
    private function isAttributeSubmitted(string $attributeId, Request $request): bool
    {
        return $request->request->has('attribute_' . $attributeId);
    }

    /**
     * Perform addition now.
     *
     * @param string     $table     The table.
     * @param Request    $request   The request.
     * @param IMetaModel $metaModel The MetaModel.
     * @param string     $parentId  The parent id.
     *
     * @return void
     */
    private function perform(string $table, Request $request, IMetaModel $metaModel, string $parentId): void
    {
        $activate = (bool) $request->request->get('activate');
        $tlclass  = (string) $request->request->get('tlclass');

        $query = $this
            ->connection
            ->createQueryBuilder()
            ->insert($table);
        foreach ($metaModel->getAttributes() as $attribute) {
            if (
                $this->knowsAttribute($attribute)
                || !($this->accepts($attribute) && $this->isAttributeSubmitted($attribute->get('id'), $request))
            ) {
                continue;
            }

            $data = [];
            foreach (
                $this->createEmptyDataFor(
                    $attribute,
                    $parentId,
                    $activate,
                    $this->startSort,
                    $tlclass
                ) as $key => $value
            ) {
                $data[$key] = ':' . $key;
                $query->setParameter($key, $value);
            }

            $query->values($data)->executeQuery();
            $this->startSort += 128;
        }

        $this->purger->purge();
    }

    /**
     * Get the current Backend referrer URL.
     *
     * @param Request $request   The request.
     * @param string  $table     The table name.
     * @param bool    $encodeAmp Flag to encode ampersands or not.
     *
     * @return string
     */
    private function getReferer(Request $request, string $table, bool $encodeAmp = false): string
    {
        $uri = $this->systemAdapter->getReferer($encodeAmp, $table);
        // Make the location an absolute URL
        if (!preg_match('@^https?://@i', $uri)) {
            $uri = $request->getBasePath() . '/' . ltrim($uri, '/');
        }

        return $uri;
    }
}
