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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Contao\InsertTag;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Input;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * This replaces the insert tag param.
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
     * If the parameter name not exist, then null will return.
     *
     * @param string $content The content.
     *
     * @return string|null
     */
    public function replace(string $content): ?string
    {
        if (false === \strpos($content, '{{')
            || !($tags = preg_split('@\{\{(.*)\}\}@', $content, -1, PREG_SPLIT_DELIM_CAPTURE))
            || (\count($tags) < 2)
        ) {
            return $content;
        }

        $newContent = null;
        foreach ($tags as $tag) {
            if (!(2 === \count($chunks = \explode('::', $tag, 2)))
                || !('param' === $chunks[0])
                || !($this->isParameterSupported($chunks[1], ['get', 'post', 'cookie', 'session', 'filter']))
            ) {
                continue;
            }
            $newContent = $this->replaceInputParameter($chunks, $content, $tag);
            $newContent = $this->replaceSessionParameter($chunks, $newContent, $tag);
        }

        return $newContent;
    }

    /**
     * Replace the insert tag with the input value.
     *
     * @param array       $chunks  The chunks.
     * @param string|null $content The content.
     * @param string      $tag     The tag.
     *
     * @return string|null
     */
    private function replaceInputParameter(array $chunks, ?string $content, string $tag): ?string
    {
        if ((null === $content)
            || !($this->isParameterSupported($chunks[1], ['get', 'post', 'cookie']))
            || !($arguments = $this->splitParameter($chunks[1]))
        ) {
            return $content;
        }

        if ((false === \strpos($tag, '&default='))) {
            if (null === ($result = $this->input->{$arguments[0]}($arguments[1]))) {
                return null;
            }
            return \str_replace(
                '{{' . $tag . '}}',
                \is_array($result) ? \serialize($result) : ($result ?? ''),
                $content
            );
        }

        $result = ($this->input->{$arguments[0]}($arguments[1]) ?: $arguments[2]);
        return \str_replace(
            '{{' . $tag . '}}',
            \is_array($result) ? \serialize($result) : ($result ?? ''),
            $content
        );
    }

    /**
     * Replace the insert tag with the session value.
     *
     * @param array       $chunks  The chunks.
     * @param string|null $content The content.
     * @param string      $tag     The tag.
     *
     * @return string|null
     */
    private function replaceSessionParameter(array $chunks, ?string $content, string $tag): ?string
    {
        if ((null === $content)
            || !($this->isParameterSupported($chunks[1], ['session']))
            || !($arguments = $this->splitParameter($chunks[1]))
        ) {
            return $content;
        }

        $sessionBag = $this->session->getBag('contao_frontend');

        if ((false === \strpos($tag, '&default='))) {
            $result = $sessionBag->get($arguments[1]);
            return \str_replace(
                '{{' . $tag . '}}',
                \is_array($result) ? \serialize($result) : ($result ?? ''),
                $content
            );
        }

        $result = ($sessionBag->get($arguments[1]) ?: $arguments[2]);
        return \str_replace(
            '{{' . $tag . '}}',
            \is_array($result) ? \serialize($result) : ($result ?? ''),
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
