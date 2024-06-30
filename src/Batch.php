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

use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\ReadableResourceStream;
use Amp\Http\Client\HttpContent;
use Amp\Http\Client\StreamedContent;
use Kafkiansky\Binary\Buffer;
use Kafkiansky\Binary\Endianness;

/**
 * @api
 */
final readonly class Batch
{
    private function __construct(
        public HttpContent $content,
    ) {
    }

    /**
     * @throws \Kafkiansky\Binary\BinaryException
     */
    public static function fromRows(Row ...$rows): self
    {
        $buffer = Buffer::empty(Endianness::little());

        foreach ($rows as $row) {
            $row->writeToBuffer($buffer);
        }

        return new self(
            StreamedContent::fromStream(
                new ReadableBuffer($buffer->reset()),
            ),
        );
    }

    /**
     * @param resource $stream
     */
    public static function fromStream($stream): self
    {
        return new self(
            StreamedContent::fromStream(
                new ReadableResourceStream($stream),
            ),
        );
    }
}
