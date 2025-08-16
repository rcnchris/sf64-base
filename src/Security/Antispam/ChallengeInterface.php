<?php

namespace App\Security\Antispam;

interface ChallengeInterface
{
    public function generateKey(): string;

    public function getSolution(string $key): mixed;

    public function verify(string $key, string $answer): bool;
}
