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

use Amp\ByteStream\BufferException;
use Amp\ByteStream\StreamException;
use Amp\Cancellation;
use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;
use Amp\NullCancellation;

/**
 * @api
 */
final readonly class Client
{
    private DelegateHttpClient $httpClient;

    /**
     * @param non-empty-string $host
     */
    public function __construct(
        private string $host,
        ?DelegateHttpClient $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?: HttpClientBuilder::buildDefault();
    }

    /**
     * @throws Exception\QueryCannotBeExecuted
     * @throws HttpException
     * @throws BufferException
     * @throws StreamException
     */
    public function insert(
        ClickHouseQuerier $query,
        Batch $batch,
        Cancellation $cancellation = new NullCancellation(),
    ): void {
        $request = new Request(
            vsprintf('%s/?input_format_values_interpret_expressions=0&query=%s', [
                ltrim($this->host, '/'),
                $query->toSQL(),
            ]),
            'POST',
            $batch->content,
        );

        $response = $this->httpClient->request($request, $cancellation);

        if ($response->getStatus() !== 200) {
            throw new Exception\QueryCannotBeExecuted(
                $response->getBody()->buffer(limit: 1024),
                $response->getHeader('X-ClickHouse-Exception-Code') ?: '',
                (string)$query,
            );
        }
    }
}
