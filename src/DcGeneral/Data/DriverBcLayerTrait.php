<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Fischer <anfischer@kaffee-partner.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformationCollection;
use MetaModels\IMetaModel;

/**
 * BC layer for translated plain MetaModels
 *
 * @internal
 *
 * @deprecated Since 2.2 to be removed in 3.0.
 */
trait DriverBcLayerTrait
{
    /**
     * Backward compatibility layer.
     *
     * @param IMetaModel $metaModel The MetaModel.
     *
     * @return DefaultLanguageInformationCollection|null
     */
    private function getLanguagesBcLayer($metaModel): ?DefaultLanguageInformationCollection
    {
        // @coverageIgnoreStart
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Translated "\MetaModel\IMetamodel" instances are deprecated since MetaModels 2.2 ' .
            'and to be removed in 3.0. The MetaModel must implement "\MetaModels\ITranslatedMetaModel".',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $collection = new DefaultLanguageInformationCollection();
        foreach ($metaModel->getAvailableLanguages() as $langCode) {
            [$langCode, $country] = explode('_', $langCode, 2);
            $collection->add(new DefaultLanguageInformation($langCode, $country ?: null));
        }

        if (count($collection) > 0) {
            return $collection;
        }

        return null;
        // @coverageIgnoreEnd
    }

    /**
     * Backward compatibility layer.
     *
     * @param IMetaModel $metaModel The MetaModel.
     *
     * @return DefaultLanguageInformation
     */
    private function getFallbackLanguageBcLayer(IMetaModel $metaModel): DefaultLanguageInformation
    {
        // @coverageIgnoreStart
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Translated "\MetaModel\IMetamodel" instances are deprecated since MetaModels 2.2 ' .
            'and to be removed in 3.0. The MetaModel must implement "\MetaModels\ITranslatedMetaModel".',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $langCode = $metaModel->getFallbackLanguage();

        [$langCode, $country] = explode('_', $langCode, 2);

        return new DefaultLanguageInformation($langCode, $country ?: null);
        // @coverageIgnoreEnd
    }
}
