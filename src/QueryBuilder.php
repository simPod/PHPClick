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

/**
 * @api
 */
final readonly class QueryBuilder implements ClickHouseQuerier
{
    /**
     * @param non-empty-string   $table
     * @param non-empty-string[] $columns
     */
    public function __construct(
        private string $table,
        private array $columns = [],
    ) {
    }

    /**
     * @param non-empty-string ...$columns
     */
    public function columns(string ...$columns): self
    {
        return new self(
            $this->table,
            \array_merge($this->columns, $columns),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toSQL(): string
    {
        return Query::string((string)$this)->toSQL();
    }


    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        /** @phpstan-var non-empty-string */
        return vsprintf('INSERT INTO %s(%s)', [
            $this->table,
            implode(', ', $this->columns),
        ]);
    }
}
