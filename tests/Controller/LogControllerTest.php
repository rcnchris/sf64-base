<?php

namespace App\Tests\Controller;

use App\Entity\Log;
use App\Tests\AppWebTestCase;

final class LogControllerTest extends AppWebTestCase
{
    public function testList(): void
    {
        $this->makeClient()->request('GET', '/log/list');
        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = $this->makeClient();
        $entity = $this->getRepository(Log::class)->findRand('l');
        $uri = sprintf('/log/show/%d', $entity->getId());
        $client->request('GET', $uri);
        self::assertResponseIsSuccessful();
    }
}
