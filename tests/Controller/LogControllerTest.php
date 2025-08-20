<?php

namespace App\Tests\Controller;

use App\Entity\Log;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class LogControllerTest extends AppWebTestCase
{
    public function testListWhenNotAuthenticated(): void
    {
        $this->assertRequestRedirectTo(
            uri: '/log/list',
            uriTo: '/security/login',
            expectedCode: Response::HTTP_FOUND
        );
    }

    public function testList(): void
    {
        $this->assertRequestIsSuccessful('/log/list', user: 'tst');
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
        $this->assertRequestIsSuccessful('/log/calendar', user: 'tst');
    }

    public function testPivottableNotAjaxRedirectToList(): void
    {
        $this->assertRequestRedirectTo(
            user: 'tst',
            uri: '/log/pivottable',
            uriTo: '/log/list',
            expectedCode: Response::HTTP_SEE_OTHER
        );
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
        $this->assertRequestIsSuccessful('/log/chart', user: 'tst');
    }
}
