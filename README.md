# Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require kafkiansky/phpclick
```

This package requires PHP 8.2 or later.

## Usage

- [From memory](#from-memory)
- [From resource](#from-resource)
- [Query builder](#query-builder)
- [Types](#types)

#### From Memory

This client is designed to be inserted into clickhouse in large batches. `RowBinary` mode is used for insertion.
This means that you must know the types of table columns and their order.

For example, let's look at the following table DDL:
```sql
CREATE TABLE IF NOT EXISTS logs (
    `LogName` String CODEC(ZSTD(1)),
    `LogTime` DateTime64(9) CODEC(Delta(8), ZSTD(1)),
    `LogAttributes` Map(LowCardinality(String), String) CODEC(ZSTD(1)),
    `LogUserId` Nullable(Int64)
)   ENGINE = MergeTree()
    ORDER BY (LogName, toUnixTimestamp(LogTime))
;
```

You can insert directly from memory using `Row`:
```php
<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Kafkiansky\PHPClick;

$client = new PHPClick\Client('http://127.0.0.1:8124/');

$client->insert(
    PHPClick\Query::string('INSERT INTO test.logs (LogName, LogTime, LogAttributes, LogUserId)'),
    PHPClick\Batch::fromRows(
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
    ),
);
```

#### From resource

Or accumulate data in a file and then paste data from it:
```php
<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Kafkiansky\PHPClick;

PHPClick\rowsToFile(
    __DIR__.'/click.log', // or resource
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
        PHPClick\Query::string('INSERT INTO test.logs (LogName, LogTime, LogAttributes, LogUserId)'),
        PHPClick\Batch::fromStream($logHandle),
    );
} finally {
    if (\is_resource($logHandle)) {
        \fclose($logHandle);
    }
}
```

#### Query builder

You can use a simple query builder (insert-only) instead of a query string:
```php
<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Kafkiansky\PHPClick;

$client = new PHPClick\Client('http://127.0.0.1:8124/');

$client->insert(
    PHPClick\Query::builder('test.logs')->columns(
        'LogName',
        'LogTime',
        'LogAttributes',
        'LogUserId',
    ),
    PHPClick\Batch::fromRows(/* rows here */),
);
```

#### Types

All available clickhouse types are listed below:

| ClickHouse Type | API Type                                  |
|-----------------|-------------------------------------------|
| `Bool`          | `\Kafkiansky\PHPClick\Column::bool`       |
| `Int8`          | `\Kafkiansky\PHPClick\Column::int8`       |
| `Uint8`         | `\Kafkiansky\PHPClick\Column::uint8`      |
| `Int16`         | `\Kafkiansky\PHPClick\Column::int16`      |
| `Uint16`        | `\Kafkiansky\PHPClick\Column::uint16`     |
| `Int32`         | `\Kafkiansky\PHPClick\Column::int32`      |
| `Uint32`        | `\Kafkiansky\PHPClick\Column::uint32`     |
| `Int64`         | `\Kafkiansky\PHPClick\Column::int64`      |
| `Uint64`        | `\Kafkiansky\PHPClick\Column::uint64`     |
| `Float32`       | `\Kafkiansky\PHPClick\Column::float32`    |
| `Float64`       | `\Kafkiansky\PHPClick\Column::float64`    |
| `String`        | `\Kafkiansky\PHPClick\Column::string`     |
| `DateTime`      | `\Kafkiansky\PHPClick\Column::dateTime`   |
| `DateTime64`    | `\Kafkiansky\PHPClick\Column::dateTime64` |
| `Nullable`      | `\Kafkiansky\PHPClick\Column::nullable`   |
| `Map`           | `\Kafkiansky\PHPClick\Column::map`        |
| `Array`         | `\Kafkiansky\PHPClick\Column::array`      |

## Testing

``` bash
$ composer test
```  

## License

The MIT License (MIT). See [License File](LICENSE) for more information.