<?php

namespace App\Message;

final class UniversityCacheWarmUpMessage
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
