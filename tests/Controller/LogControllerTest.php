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

    public function testCalendar(): void
    {
        $this->makeClient()->request('GET', '/log/calendar');
        self::assertResponseIsSuccessful();
    }

    public function testPivottableNotAjaxRedirectToList(): void 
    {
        $this->makeClient()->request('GET', '/log/pivottable');
        self::assertResponseRedirects('/log/list');
    }

    public function testPivottableInAjax(): void 
    {
        $client = $this->makeClient();
        $client->xmlHttpRequest('GET', '/log/pivottable');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $results = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals(0, json_last_error(), 'Erreur structure JSON');
        self::assertIsArray($results);
        $this->assertArrayHasKeys($results, ['rows', 'cols', 'aggregate', 'items']);
    }
}
