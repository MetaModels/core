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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\InsertTag;

use LogicException;

use function preg_match;
use function strlen;
use function strpos;
use function substr;

final class Parser
{
    private string $content;
    private int $length;
    private int $position;

    public function __construct(string $content)
    {
        $this->content  = $content;
        $this->position = 0;
        $this->length   = strlen($this->content);
    }

    public static function parse(string $content): NodeList
    {
        $instance = new self($content);

        return $instance->parseInternal();
    }

    private function parseInternal(): NodeList
    {
        $buffer = [];

        while (null !== $nextOpening = $this->findNextOpenTagPosition()) {
            if (null !== $nextOpening) {
                if ($nextOpening > $this->position) {
                    $buffer[] = new LiteralNode($this->read($nextOpening - $this->position));
                }
                $buffer[] = $this->readTag();
            }
        }

        if ($this->position < $this->length) {
            // Add remaining contents as literal.
            $buffer[] = new LiteralNode(substr($this->content, $this->position));
        }

        return new NodeList(...$buffer);
    }

    private function readTag(): NodeInterface
    {
        if (!$this->isAtOpening()) {
            throw new LogicException('Expected to be at opening of insert tag.');
        }
        $this->read(2);
        // Read anything up to the next open or close tag.
        $parts = [];
        $nextOpening = $this->findNextOpenTagPosition();
        $nextClosing = $this->findNextCloseTagPosition();
        // Ok, here we have to check what comes first:
        // 1. another open tag (we are already within a tag) => we have a nested tag.
        if ((null !== $nextOpening) && $nextOpening < $nextClosing) {
            if ($nextOpening > $this->position) {
                $parts[] = new LiteralNode($this->read($nextOpening - $this->position));
            }
            $parts[] = $this->readTag();
        }
        // Tag is closed, end here.
        if ($this->isAtClosing()) {
            $this->read(2);
            return new Node(...$parts);
        }

        // 2. a close tag => this tag is complete.
        if ($nextClosing > $this->position) {
            $parts[] = new LiteralNode($this->read($nextClosing - $this->position));
        }

        if (!$this->isAtClosing()) {
            throw new LogicException('Expected to be at closing of insert tag.');
        }
        $this->read(2);

        return new Node(...$parts);
    }

    private function peek(int $count): string
    {
        return substr($this->content, $this->position, $count);
    }

    private function read(int $count): string
    {
        $data = $this->peek($count);
        $this->position += $count;

        return $data;
    }

    private function findNextOpenTagPosition(): ?int
    {
        if (false === $next = strpos($this->content, '{{', $this->position)) {
            return null;
        }
        return $next;
    }

    private function findNextCloseTagPosition(): ?int
    {
        if (false === $next = strpos($this->content, '}}', $this->position)) {
            return null;
        }
        return $next;
    }

    private function isAtClosing(): bool
    {
        return '}}' === $this->peek(2);
    }

    private function isAtOpening(): bool
    {
        return '{{' === $this->peek(2);
    }
}
