<?php

namespace App\Tests\Entity;

use App\Entity\Token;
use App\Repository\TokenRepository;
use App\Tests\AppKernelTestCase;

final class TokenEntityTest extends AppKernelTestCase
{
    private function getRepo(): TokenRepository
    {
        return $this->getRepository(TokenRepository::class);
    }

    private function getLastInsert(): Token
    {
        return $this->getRepo()->getLastInsert();
    }

    public function testCreate(): void
    {
        $start = new \DateTimeImmutable();
        $end = $start->modify('+1 hours');
        $data = [
            'user' => $this->getUserRepository()->findRand('u'),
            'token' => uniqid(),
            'start' => $start,
            'end' => $end,
        ];

        $count = $this->getRepo()->count([]);
        $entity = new Token();
        $entity
            ->setUser($data['user'])
            ->setToken($data['token'])
            ->setStartAt($data['start'])
            ->setEndAt($data['end']);
        $this->getRepo()->save($entity);
        self::assertEquals($count + 1, $this->getRepo()->count([]));

        $token = $this->getLastInsert();
        self::assertEquals($data['user'], $token->getUser());
        self::assertSame($data['token'], $token->getToken());

        // Identifiant
        self::assertIsInt($token->getId());

        // Champs calculés
        self::assertSame($data['token'], (string)$token);

        // Traits
        $this->assertEntityUseDatefieldTrait($token);
        $this->assertEntityUseIntervalFieldTrait($token);
    }
}
