<?php

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
        ?Cancellation $cancellation = null,
    ): void {
        $request = new Request(
            vsprintf('%s/?input_format_values_interpret_expressions=0&query=%s', [
                ltrim($this->host, '/'),
                $query->toSQL(),
            ]),
            'POST',
            $batch->content,
        );

        $response = $this->httpClient->request($request, $cancellation ?: new NullCancellation());

        if ($response->getStatus() !== 200) {
            throw new Exception\QueryCannotBeExecuted(
                $response->getBody()->buffer(limit: 1024),
                $response->getHeader('X-ClickHouse-Exception-Code') ?: '',
                (string)$query,
            );
        }
    }
}
