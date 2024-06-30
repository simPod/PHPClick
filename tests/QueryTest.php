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

namespace Kafkiansky\PHPClick\Tests;

use Kafkiansky\PHPClick\Query;
use Kafkiansky\PHPClick\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
#[CoversClass(QueryBuilder::class)]
final class QueryTest extends TestCase
{
    public function testQueryString(): void
    {
        self::assertSame(\rawurlencode('INSERT INTO test.logs (LogName) Format RowBinary'), Query::string('INSERT INTO test.logs (LogName)')->toSQL());
    }

    public function testQueryBuilder(): void
    {
        self::assertSame(
            \rawurlencode('INSERT INTO test.logs(LogName, LogTime, LogAttributes) Format RowBinary'),
            Query::builder('test.logs')->columns('LogName', 'LogTime')->columns('LogAttributes')->toSQL(),
        );
    }
}
