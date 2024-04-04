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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Contao\InsertTag;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

/**
 * This replaces the insert tag param.
 */
final class ReplaceParam
{
    /**
     * The input framework.
     *
     * @var ContaoFramework
     */
    private ContaoFramework $framework;

    /**
     * The adapter.
     *
     * @var Adapter|null
     */
    private ?Adapter $input = null;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * ReplaceParam constructor.
     *
     * @param ContaoFramework $framework    The input framework.
     * @param RequestStack    $requestStack The session.
     */
    public function __construct(ContaoFramework $framework, RequestStack $requestStack)
    {
        $this->framework    = $framework;
        $this->requestStack = $requestStack;
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
        $tags = [];
        if (
            !\str_contains($content, '{{')
            || !($tags = preg_split('@\{\{(.*)\}\}@', $content, -1, PREG_SPLIT_DELIM_CAPTURE))
            || (\count($tags) < 2)
        ) {
            return $content;
        }

        $newContent = null;
        foreach ($tags as $tag) {
            if (
                !(2 === \count($chunks = \explode('::', $tag, 2)))
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
     * @param list<string> $chunks  The chunks.
     * @param string|null  $content The content.
     * @param string       $tag     The tag.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function replaceInputParameter(array $chunks, ?string $content, string $tag): ?string
    {
        if (null === ($arguments = $this->getArguments($chunks[1], $content, ['get', 'post', 'cookie']))) {
            return $content;
        }
        assert(\is_string($content));

        if (null === $this->input) {
            /** @psalm-suppress InternalMethod - Class ContaoFramework is internal, not the getAdapter() method. */
            $this->input = $this->framework->getAdapter(Input::class);
        }

        if ((!\str_contains($tag, '&default='))) {
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
     * @param list<string> $chunks  The chunks.
     * @param string|null  $content The content.
     * @param string       $tag     The tag.
     *
     * @return string|null
     */
    private function replaceSessionParameter(array $chunks, ?string $content, string $tag): ?string
    {
        if (null === ($arguments = $this->getArguments($chunks[1], $content, ['session']))) {
            return $content;
        }
        assert(\is_string($content));

        $sessionBag = $this->requestStack->getSession()->getBag('contao_frontend');
        assert($sessionBag instanceof AttributeBagInterface);

        if ((!\str_contains($tag, '&default='))) {
            $result = $sessionBag->get($arguments[1]);
            return \str_replace(
                '{{' . $tag . '}}',
                \is_array($result) ? \serialize($result) : (string) $result,
                $content
            );
        }

        $result = ($sessionBag->get($arguments[1]) ?: $arguments[2]);
        return \str_replace(
            '{{' . $tag . '}}',
            \is_array($result) ? \serialize($result) : (string) $result,
            $content
        );
    }

    /**
     * @param list<string> $supported
     *
     * @return list<string>|null
     */
    private function getArguments(string $chunk, ?string $content, array $supported): ?array
    {
        if ((null === $content) || !$this->isParameterSupported($chunk, $supported)) {
            return null;
        }
        $arguments = $this->splitParameter($chunk);
        if ((null === $arguments) || ([] === $arguments)) {
            return null;
        }

        return $arguments;
    }

    /**
     * Split the parameter.
     *
     * @param string $parameter The parameter.
     *
     * @return list<string>|null
     */
    private function splitParameter(string $parameter): ?array
    {
        if (
            (2 !== \count($chunks = \explode('?', $parameter)))
            || (!\str_starts_with($chunks[1], 'name='))
        ) {
            return null;
        }

        if (!\str_contains($chunks[1], '&default=')) {
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
            if (!\str_starts_with($parameter, $name)) {
                continue;
            }

            $isSupported = true;
            break;
        }

        return $isSupported;
    }
}
