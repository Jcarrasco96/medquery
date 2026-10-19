<?php

declare(strict_types=1);

namespace app\models;

final class ProcedureLookupResult
{

    public function __construct(
        public int $index,
        public string $from_date,
        public string $to_date,
        public string $procedure,
        public string $m1,
        public string $m2,
        public string $m3,
        public string $m4,
        public int $units,
    )
    {
    }

}