<?php

/**
 * MIT License
 * Copyright (c) 2024 kafkiansky.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Kafkiansky\PHPClick;

use Kafkiansky\Binary\Buffer;
use Kafkiansky\Binary\Endianness;

/**
 * @api
 */
final readonly class Row
{
    /**
     * @param ColumnValuer[] $columns
     */
    private function __construct(
        private array $columns = [],
    ) {
    }

    public static function columns(ColumnValuer ...$columns): self
    {
        return new self($columns);
    }

    public function writeToBuffer(Buffer $buffer): void
    {
        foreach ($this->columns as $column) {
            $column->write($buffer);
        }
    }

    /**
     * @param resource $stream
     *
     * @throws \Kafkiansky\Binary\BinaryException
     * @throws Exception\StreamNotWriteable
     */
    public function streamTo($stream, ?Buffer $buffer = null): void
    {
        $buffer ??= Buffer::empty(Endianness::little());

        $this->writeToBuffer($buffer);

        // We do not modify the buffer so that it will continue to be available if it is passed externally.
        if (!\fwrite($stream, $buffer->read(\count($buffer)))) {
            throw new Exception\StreamNotWriteable(stream_get_meta_data($stream)['uri']);
        }
    }
}
