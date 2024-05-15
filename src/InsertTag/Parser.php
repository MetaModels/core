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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\InsertTag;

use LogicException;

use function strlen;
use function strpos;
use function substr;

/**
 * Parses an arbitrary string into a node list for insert tags.
 */
final class Parser
{
    /**
     * The input buffer to parse.
     *
     * @var string
     */
    private string $content;

    /**
     * The total length of the input buffer.
     *
     * @var int
     */
    private int $length;

    /**
     * The current parser position.
     *
     * @var int
     */
    private int $position;

    /**
     * Private constructor.
     *
     * This class should be used from parse() only.
     *
     * @param string $content The content to parse.
     */
    private function __construct(string $content)
    {
        $this->content  = $content;
        $this->position = 0;
        $this->length   = strlen($this->content);
    }

    /**
     * Parse the passed buffer.
     *
     * @param string $content The content to parse.
     *
     * @return NodeList
     */
    public static function parse(string $content): NodeList
    {
        return (new self($content))->parseInternal();
    }

    /**
     * Parse the input buffer - called from static method parse().
     *
     * @return NodeList
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function parseInternal(): NodeList
    {
        $buffer = [];

        while (null !== ($nextOpening = $this->findNextOpenTagPosition())) {
            if ($nextOpening > $this->position) {
                $buffer[] = new LiteralNode($this->read($nextOpening - $this->position));
            }
            $buffer[] = $this->readTag();
        }

        if ($this->position < $this->length) {
            // Add remaining contents as literal.
            $buffer[] = new LiteralNode(substr($this->content, $this->position));
        }

        return new NodeList(...$buffer);
    }

    /**
     * Reads a tag from the input buffer.
     *
     * @return Node
     *
     * @throws LogicException When the insert tag is not properly structured.
     */
    private function readTag(): Node
    {
        if (!$this->isAtOpening()) {
            throw new LogicException('Expected to be at opening of insert tag.');
        }

        $this->read(2);
        // Read anything up to the next open or close tag.
        $parts       = [];
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
        if ((null !== $nextClosing) && $nextClosing > $this->position) {
            $parts[] = new LiteralNode($this->read($nextClosing - $this->position));
        }

        if (!$this->isAtClosing()) {
            throw new LogicException('Expected to be at closing of insert tag.');
        }
        $this->read(2);

        return new Node(...$parts);
    }

    /**
     * Read the given amount of bytes from the input.
     *
     * @param int $count The amount of bytes to read.
     *
     * @return string
     */
    private function peek(int $count): string
    {
        return substr($this->content, $this->position, $count);
    }

    /**
     * Read the given amount of bytes from the input and advance the reading position.
     *
     * @param int $count The amount of bytes to read.
     *
     * @return string
     */
    private function read(int $count): string
    {
        $data = $this->peek($count);

        $this->position += $count;

        return $data;
    }

    /**
     * Find the index of the next insert tag opener.
     *
     * @return int|null
     */
    private function findNextOpenTagPosition(): ?int
    {
        if (false === $next = strpos($this->content, '{{', $this->position)) {
            return null;
        }

        return $next;
    }

    /**
     * Find the index of the next insert tag closer.
     *
     * @return int|null
     */
    private function findNextCloseTagPosition(): ?int
    {
        if (false === $next = strpos($this->content, '}}', $this->position)) {
            return null;
        }

        return $next;
    }

    /**
     * Check if we are at an insert tag closer.
     *
     * @return bool
     */
    private function isAtClosing(): bool
    {
        return '}}' === $this->peek(2);
    }

    /**
     * Check if we are at an insert tag opener.
     *
     * @return bool
     */
    private function isAtOpening(): bool
    {
        return '{{' === $this->peek(2);
    }
}
