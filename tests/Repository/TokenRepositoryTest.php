<?php

namespace App\Tests\Repository;

use App\Entity\Token;
use App\Repository\TokenRepository;
use App\Tests\AppKernelTestCase;

final class TokenRepositoryTest extends AppKernelTestCase
{
    private function getRepo(): TokenRepository
    {
        return $this->getRepository(TokenRepository::class);
    }

    public function testGetUserByToken(): void
    {
        $user = $this->getRepo()->getUserByToken('');
        self::assertNull($user);
    }

    public function testRemoveExpired(): void
    {
        $start = new \DateTimeImmutable('2025-07-29 00:00:00');
        $token = (new Token())
            ->setUser($this->getUserRepository()->getByPseudoOrEmail('tst'))
            ->setToken(uniqid())
            ->setStartAt($start)
            ->setEndAt($start->modify('+1 hours'));
        $this->getRepo()->save($token);
        self::assertGreaterThan(0, $this->getRepo()->removeExpired());
    }
}
