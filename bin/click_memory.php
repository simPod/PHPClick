<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Kafkiansky\PHPClick;

PHPClick\rowsToFile(
    __DIR__.'/click.log',
    PHPClick\Row::columns(
        PHPClick\Column::string('users'),
        PHPClick\Column::dateTime64(new \DateTimeImmutable()),
        PHPClick\Column::map(
            [PHPClick\Column::string('x'), PHPClick\Column::string('y')],
        ),
        PHPClick\Column::nullable(
            PHPClick\Column::int64(13),
        ),
    ),
    PHPClick\Row::columns(
        PHPClick\Column::string('users'),
        PHPClick\Column::dateTime64(new \DateTimeImmutable()),
        PHPClick\Column::map(
            [PHPClick\Column::string('x'), PHPClick\Column::string('y')],
        ),
        PHPClick\Column::nullable(),
    ),
);

$client = new PHPClick\Client('http://127.0.0.1:8124/');

$logHandle = fopen(__DIR__.'/click.log', 'r');

try {
    $client->insert(
        PHPClick\Query::builder('test.logs')->columns(
            'LogName',
            'LogTime',
            'LogAttributes',
            'LogUserId',
        ),
        PHPClick\Batch::fromStream($logHandle),
    );
} finally {
    if (\is_resource($logHandle)) {
        \fclose($logHandle);
    }
}
