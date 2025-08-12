<?php

namespace App\DTO;

final class TablettesDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $icon,
        public readonly string $color,
        public readonly ?string $description,
        public readonly ?int $children,
    ) {}
}