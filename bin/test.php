<?php declare(strict_types=1);

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Kafkiansky\PHPClick\Column;
use Kafkiansky\PHPClick\Row;

require_once __DIR__.'/../vendor/autoload.php';

$row = Row::columns(
    Column::string('KEK'),
    Column::string('3e9f74da-7d69-48c9-bc1a-4b2af2d3f487'),
    Column::string('payment_succeeded'),
    Column::dateTime(new \DateTimeImmutable('NOW')),
    Column::map(
        [Column::string('x'), Column::string('y')]
    ),
);

$rows = [];

for ($i = 0; $i < 1_000_000; $i++) {
    $rows[] = Row::columns(
        Column::string('KEK'),
        Column::string('3e9f74da-7d69-48c9-bc1a-4b2af2d3f487'),
        Column::string('payment_succeeded'),
        Column::dateTime(new \DateTimeImmutable('NOW')),
        Column::map(
            [Column::string('x'), Column::string('y')]
        ),
    );
}

$client = new \Kafkiansky\PHPClick\Client('http://127.0.0.1:8124/');

$client->insert(
    \Kafkiansky\PHPClick\Query::string('INSERT INTO proto.logs (LogName, LogTime, LogAttributes, LogUserId)'),
    \Kafkiansky\PHPClick\Batch::fromRows(
        Row::columns(
            Column::string('users'),
            Column::dateTime64(new \DateTimeImmutable()),
            Column::map(
                [Column::string('x'), Column::string('y')],
            ),
            Column::nullable(
                Column::int64(13),
            ),
        ),
        Row::columns(
            Column::string('users'),
            Column::dateTime64(new \DateTimeImmutable()),
            Column::map(
                [Column::string('x'), Column::string('y')],
            ),
            Column::nullable(),
        ),
    ),
);
dd(1);

$buffer = \Kafkiansky\Binary\Buffer::empty(\Kafkiansky\Binary\Endianness::little());
$row->writeToBuffer($buffer);
$row->writeToBuffer($buffer);
$row->writeToBuffer($buffer);

$client = (new HttpClientBuilder())
//    ->intercept(new \Amp\Http\Client\Interceptor\ModifyRequest(function (Request $request): Request {
//        $request->setUri('http://127.0.0.1:8124');
//        return $request;
//    }))
    ->build()
;

$uri = \sprintf("http://127.0.0.1:8124/?input_format_values_interpret_expressions=0&query=%s", rawurlencode('INSERT INTO proto.events (AppId, SessionId, EventName, EventTime, EventAttributes) FORMAT RowBinary'));

$fp = \fopen(__DIR__.'/click.dump', 'a+');

$req = new Request($uri, 'POST', $buffer->reset());

$response = $client->request($req, new \Amp\TimeoutCancellation(1));
dd($response->getStatus(), $response->getHeader('X-ClickHouse-Exception-Code'), $response->getBody()->buffer(limit: 1024));