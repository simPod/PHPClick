<?php

declare(strict_types=1);

namespace Kafkiansky\PHPClick\Exception;

final class QueryCannotBeExecuted extends \Exception
{
    public function __construct(
        public readonly string $clickHouseException,
        public readonly string $clickHouseErrorCode,
        public readonly string $query,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            vsprintf('Query "%s" cannot be executed due to exception (%s): "%s".', [
                $this->query,
                $this->clickHouseErrorCode,
                $this->clickHouseException,
            ]),
            previous: $previous,
        );
    }
}
