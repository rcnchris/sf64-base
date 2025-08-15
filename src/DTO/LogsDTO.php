<?php

namespace App\DTO;

final class LogsDTO
{
    public function __construct(
        public readonly int $id,
        public readonly \DateTimeImmutable $createdAt,
        public readonly string $message,
        public readonly string $level,
        public readonly string $channel,
        public readonly ?string $user = null,
        public readonly ?string $route = null,
    ) {}
}