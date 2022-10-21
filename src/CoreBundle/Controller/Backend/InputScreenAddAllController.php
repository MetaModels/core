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

namespace MetaModels\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Framework\Adapter;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IInternal;
use MetaModels\BackendIntegration\PurgeCache;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

/**
 * This controller provides the add-all action in input screens.
 */
class InputScreenAddAllController extends AbstractAddAllController
{
    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The twig engine.
     *
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * Create a new instance.
     *
     * @param TwigEnvironment     $twig          The templating instance.
     * @param TranslatorInterface $translator    The translator.
     * @param IFactory            $factory       The MetaModels factory.
     * @param Connection          $connection    The database connection.
     * @param Adapter             $systemAdapter Adapter to the Contao\System class.
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
        parent::__construct($twig, $translator, $factory, $connection, $systemAdapter, $purger);

        $this->translator = $translator;
    }

    /**
     * Invoke this.
     *
     * @param string  $metaModel   The MetaModel name.
     * @param string  $inputScreen The input screen id.
     * @param Request $request     The request.
     *
     * @return Response
     */
    public function __invoke($metaModel, $inputScreen, Request $request)
    {
        return $this->process('tl_metamodel_dcasetting', $metaModel, $inputScreen, $request);
    }

    /**
     * Render the output array for the template.
     *
     * @param string     $table     The name of the table to add to.
     * @param IMetaModel $metaModel The MetaModel name on which to work on.
     * @param Request    $request   The request.
     *
     * @return array
     */
    protected function render($table, $metaModel, Request $request)
    {
        return \array_merge(
            parent::render($table, $metaModel, $request),
            ['tlclass' => $this->translator->trans($table . '.addAll_tlclass', [], 'contao_' . $table)]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function createEmptyDataFor(IAttribute $attribute, $parentId, $activate, $sort, $tlclass = '')
    {
        return [
            'dcatype'   => 'attribute',
            'tl_class'  => $tlclass,
            'attr_id'   => $attribute->get('id'),
            'pid'       => $parentId,
            'sorting'   => $sort,
            'tstamp'    => time(),
            'published' => $activate ? '1' : ''
        ];
    }

    /**
     * Test if the passed attribute is acceptable.
     *
     * @param IAttribute $attribute The attribute to check.
     *
     * @return bool
     */
    protected function accepts(IAttribute $attribute)
    {
        return !($attribute instanceof IInternal) && !empty($attribute->get('id'));
    }
}
