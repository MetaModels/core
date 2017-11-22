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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use MetaModels\Attribute\IAttribute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller provides the add-all action in input screens.
 */
class InputScreenAddAllController extends AbstractAddAllController
{
    /**
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
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function createEmptyDataFor(IAttribute $attribute, $parentId, $activate, $sort)
    {
        return [
            'dcatype'   => 'attribute',
            'tl_class'  => '',
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
        return !empty($attribute->get('id'));
    }
}
