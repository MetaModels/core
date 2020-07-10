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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Contao\InsertTag;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Input;
use Contao\Session;

/**
 * This replace the insert tag param.
 */
final class ReplaceParam
{
    /**
     * The input.
     *
     * @var Input
     */
    private $input;

    /**
     * The session.
     *
     * @var Session
     */
    // FIXME: Replace the deprecated session.
    private $session;

    /**
     * ReplaceParam constructor.
     *
     * @param Adapter $input   The input.
     * @param Session $session The session.
     */
    public function __construct(Adapter $input, Session $session)
    {
        $this->input   = $input;
        $this->session = $session;
    }

    /**
     * Replace the param insert tag in the given content.
     *
     * @param string $content The content.
     *
     * @return string
     */
    public function replace(string $content)
    {
        if (false === \strpos($content, '{{')
            || !($tags = preg_split('~{{([a-zA-Z0-9\x80-\xFF][^{}]*)}}~', $content, -1, PREG_SPLIT_DELIM_CAPTURE))
            || (\count($tags) < 2)
        ) {
            return $content;
        }

        foreach ($tags as $tag) {
            if (!(2 === \count($chunks = \explode('::', $tag)))
                || !('param' === $chunks[0])
                || !$this->isParameterSupported($chunks[1], ['get', 'post', 'cookie', 'session', 'filter'])
            ) {
                continue;
            }

            $content = $this->replaceInputParameter($chunks, $content, $tag);
            $content = $this->replaceSessionParameter($chunks, $content, $tag);
        }

        return $content;
    }

    /**
     * Replace the insert tag with the input value.
     *
     * @param array  $chunks  The chunks.
     * @param string $content The content.
     * @param string $tag     The tag.
     *
     * @return string
     */
    private function replaceInputParameter(array $chunks, string $content, string $tag): string
    {
        if (!$this->isParameterSupported($chunks[1], ['get', 'post', 'cookie'])
            || !($arguments = $this->splitParameter($chunks[1]))
        ) {
            return $content;
        }

        if ((false === \strpos($tag, '&default='))) {
            $result = $this->input->{$arguments[0]}($arguments[1]);
            return \str_replace(
                '{{' . $tag . '}}',
                \is_array($result) ? \serialize($result) : $result,
                $content
            );
        }

        $result = ($this->input->{$arguments[0]}($arguments[1]) ?: $arguments[2]);
        return \str_replace(
            '{{' . $tag . '}}',
            \is_array($result) ? \serialize($result) : $result,
            $content
        );
    }

    /**
     * Replace the insert tag with the session value.
     *
     * @param array  $chunks  The chunks.
     * @param string $content The content.
     * @param string $tag     The tag.
     *
     * @return string
     */
    private function replaceSessionParameter(array $chunks, string $content, string $tag): string
    {
        if (!$this->isParameterSupported($chunks[1], ['session'])
            || !($arguments = $this->splitParameter($chunks[1]))
        ) {
            return $content;
        }

        if ((false === \strpos($tag, '&default='))) {
            $result = $this->session->get($arguments[1]);
            return \str_replace(
                '{{' . $tag . '}}',
                \is_array($result) ? \serialize($result) : $result,
                $content
            );
        }

        $result = ($this->session->get($arguments[1]) ?: $arguments[2]);
        return \str_replace(
            '{{' . $tag . '}}',
            \is_array($result) ? \serialize($result) : $result,
            $content
        );
    }

    /**
     * Split the parameter.
     *
     * @param string $parameter The parameter.
     *
     * @return array|null
     */
    private function splitParameter(string $parameter): ?array
    {
        if (!(2 === \count($chunks = \explode('?', $parameter)))
            || !(0 === \strpos($chunks[1], 'name='))
        ) {
            return null;
        }

        if (false === \strpos($chunks[1], '&default=')) {
            return [$chunks[0], \substr($chunks[1], \strlen('name='))];
        }

        $subChunk  = \explode('&', $chunks[1]);
        $chunks[]  = \substr($subChunk[1], \strlen('default='));
        $chunks[1] = \substr($subChunk[0], \strlen('name='));

        return $chunks;
    }

    /**
     * Detect if the parameter is supported.
     *
     * @param string $parameter The parameter.
     * @param array  $supported The supported parameter.
     *
     * @return bool
     */
    private function isParameterSupported(string $parameter, array $supported): bool
    {
        $isSupported = false;
        foreach ($supported as $name) {
            if (0 !== \strpos($parameter, $name)) {
                continue;
            }

            $isSupported = true;
            break;
        }

        return $isSupported;
    }
}
