<?php

namespace App\Tests\Controller;

use App\Entity\Log;
use App\Tests\AppWebTestCase;

final class LogControllerTest extends AppWebTestCase
{
    public function testListWhenNotAuthenticated(): void
    {
        $this->makeClient()->request('GET', '/log/list');
        self::assertResponseRedirects('/security/login');
    }

    public function testList(): void
    {
        $this->makeClient('tst')->request('GET', '/log/list');
        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = $this->makeClient('tst');
        $entity = $this->getRepository(Log::class)->findRand('l');
        $uri = sprintf('/log/show/%d', $entity->getId());
        $client->request('GET', $uri);
        self::assertResponseIsSuccessful();
    }

    public function testCalendar(): void
    {
        $this->makeClient('tst')->request('GET', '/log/calendar');
        self::assertResponseIsSuccessful();
    }

    public function testPivottableNotAjaxRedirectToList(): void 
    {
        $this->makeClient('tst')->request('GET', '/log/pivottable');
        self::assertResponseRedirects('/log/list');
    }

    public function testPivottableInAjax(): void 
    {
        $client = $this->makeClient('tst');
        $client->xmlHttpRequest('GET', '/log/pivottable');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $results = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals(0, json_last_error(), 'Erreur structure JSON');
        self::assertIsArray($results);
        $this->assertArrayHasKeys($results, ['rows', 'cols', 'aggregate', 'items']);
    }

    public function testChart(): void
    {
        $this->makeClient('tst')->request('GET', '/log/chart');
        self::assertResponseIsSuccessful();
    }
}
