<?php

declare(strict_types=1);

namespace Kafkiansky\PHPClick\Tests;

use Amp\Http\Client\DelegateHttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Kafkiansky\Binary\Buffer;
use Kafkiansky\Binary\Endianness;
use Kafkiansky\PHPClick\Batch;
use Kafkiansky\PHPClick\Client;
use Kafkiansky\PHPClick\Column;
use Kafkiansky\PHPClick\Exception\QueryCannotBeExecuted;
use Kafkiansky\PHPClick\Query;
use Kafkiansky\PHPClick\Row;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
final class ClientTest extends TestCase
{
    public function testQueryExecuted(): void
    {
        $httpClient = $this->createMock(DelegateHttpClient::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with(new Callback(function (Request $request): bool {
                $buf = Buffer::empty(Endianness::little());
                Column::string('test')->write($buf);

                self::assertSame(sprintf('http://127.0.0.1:8123/?input_format_values_interpret_expressions=0&query=%s', \rawurlencode('INSERT INTO test.logs(LogName) Format RowBinary')), (string)$request->getUri());
                self::assertSame($buf->reset(), $request->getBody()->getContent()->read());

                return true;
            }))
            ->willReturn(new Response(protocolVersion: '1.1', status: 200, reason: null, headers: [], body: null, request: new Request('')))
        ;

        $client = new Client(
            'http://127.0.0.1:8123',
            $httpClient,
        );

        $client->insert(Query::string('INSERT INTO test.logs(LogName)'), Batch::fromRows(Row::columns(Column::string('test'))));
    }

    public function testQueryFailed(): void
    {
        $httpClient = $this->createMock(DelegateHttpClient::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(new Response(protocolVersion: '1.1', status: 500, reason: null, headers: ['X-ClickHouse-Exception-Code' => '16'], body: 'Code: 16. DB::Exception: No such column LogName in table test.logs (af235e5b-cada-47b6-a0ac-7872b09843cf). (NO_SUCH_COLUMN_IN_TABLE) (version 24.5.1.1763 (official build))', request: new Request('')))
        ;

        $client = new Client(
            'http://127.0.0.1:8123',
            $httpClient,
        );

        self::expectException(QueryCannotBeExecuted::class);
        self::expectExceptionMessage('INSERT INTO test.logs(LogName)" cannot be executed due to exception (16): "Code: 16. DB::Exception: No such column LogName in table test.logs (af235e5b-cada-47b6-a0ac-7872b09843cf). (NO_SUCH_COLUMN_IN_TABLE) (version 24.5.1.1763 (official build))".');
        $client->insert(Query::string('INSERT INTO test.logs(LogName)'), Batch::fromRows(Row::columns(Column::string('test'))));
    }
}
