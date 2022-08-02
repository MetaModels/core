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

namespace MetaModels\CoreBundle\Translator;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

final class MetaModelTranslationLoader implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        dump($resource, $locale, $domain);
        $catalog = new MessageCatalogue($locale);
        if ($domain === 'mm_testselecttags') {
            $catalog->set('mm_testselecttags.select_normal.0', 'Label !!!!', $domain);
            $catalog->set('mm_testselecttags.select_normal.1', 'Description', $domain);
        }
        dump($catalog);

        return $catalog;
    }
}
