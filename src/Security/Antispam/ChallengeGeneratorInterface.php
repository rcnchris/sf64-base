<?php

namespace App\Security\Antispam;

use Symfony\Component\HttpFoundation\Response;

interface ChallengeGeneratorInterface
{
    public function generate(string $key): Response;
}
