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

namespace MetaModels\CoreBundle\Controller\Backend;

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IInternal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller provides the add-all action in render settings.
 */
class RenderSettingAddAllController extends AbstractAddAllController
{
    /**
     * Invoke this.
     *
     * @param string  $metaModel     The MetaModel name.
     * @param string  $renderSetting The render setting id.
     * @param Request $request       The request.
     *
     * @return Response
     */
    public function __invoke($metaModel, $renderSetting, Request $request)
    {
        return $this->process('tl_metamodel_rendersetting', $metaModel, $renderSetting, $request);
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function createEmptyDataFor(IAttribute $attribute, $parentId, $activate, $sort, $tlclass = '')
    {
        $result   = [
            'attr_id' => $attribute->get('id'),
            'pid'     => $parentId,
            'sorting' => $sort,
            'tstamp'  => time(),
            'enabled' => $activate ? '1' : ''
        ];
        $defaults = $attribute->getDefaultRenderSettings();
        foreach ($defaults->getKeys() as $key) {
            $result[$key] = $defaults->get($key);
        }

        return $result;
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
