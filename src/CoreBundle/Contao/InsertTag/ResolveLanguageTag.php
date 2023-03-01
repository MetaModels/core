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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Oliver Willmes <info@oliverwillmes.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Contao\InsertTag;

use Contao\StringUtil;
use Contao\System;

/**
 * Check and find iflng / ifnlng inserttags and resolve query string.
 */
final class ResolveLanguageTag
{
    /**
     * Resolve iflng / ifnlng inserttag in query string.
     *
     * @param string $queryString
     *
     * @return string
     */
    public function resolve(string $queryString): string
    {
        if (\strpos($queryString, '{{iflng') === false && \strpos($queryString, '{{ifnlng') === false)
        {
            return $queryString;
        }
        $tags = \preg_split('~{{(ifn?lng[^{}]*)}}~', $queryString, -1,PREG_SPLIT_DELIM_CAPTURE );

        $strBuffer = '';

        for ($_rit=0, $_cnt=\count($tags); $_rit<$_cnt; $_rit+=2)
        {
            $strBuffer .= $tags[$_rit];

            if (!isset($tags[$_rit+1]))
            {
                continue;
            }
            $strTag = $tags[$_rit+1];

            if (!$strTag)
            {
                continue;
            }

            $flags = \explode('|', $strTag);
            $tag = \array_shift($flags);
            $elements = \explode('::', $tag);

            $arrCache[$strTag] = '';

            if (!empty($elements[1]) && $this->languageMatches($elements[1]) === (\strtolower($elements[0]) === 'ifnlng'))
            {
                for (; $_rit<$_cnt; $_rit+=2)
                {
                    if (1 === \preg_match('/^' . \preg_quote($elements[0], '/') . '(?:$|::|\|)/i', $tags[$_rit+3] ?? ''))
                    {
                        $tags[$_rit+2] = '';
                        break;
                    }
                }
            }
            unset($arrCache[$strTag]);

            $strBuffer .= $arrCache[$strTag] ?? '';
        }

        return $strBuffer;
    }

    /**
     * Check if the language matches.
     *
     * @param string $language
     *
     * @return boolean
     */
    private function languageMatches($language)
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        $pageModel = $request->attributes->get('pageModel');

        foreach (StringUtil::trimsplit(',', $language) as $lang)
        {
            if ($pageModel->language === $lang)
            {
                return true;
            }

            if (substr($lang, -1) === '*' && 0 === strncmp($pageModel->language, $lang, \strlen($lang) - 1))
            {
                return true;
            }
        }

        return false;
    }
}