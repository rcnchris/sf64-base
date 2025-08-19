<?php

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;

final class DemoControllerTest extends AppWebTestCase
{
    const URI = '/demo/pdf';

    public function testPdfAppPdf(): void
    {
        $this->makeClient()->request('GET', self::URI);
        self::assertResponseIsSuccessful();
    }

    public function testPdfBookmarksToc(): void
    {
        $this->makeClient()->request('GET', self::URI, ['name' => 'bookmark']);
        self::assertResponseIsSuccessful();
    }

    public function testPdfFile(): void
    {
        $this->makeClient()->request('GET', self::URI, ['name' => 'file']);
        self::assertResponseIsSuccessful();
    }

    public function testPdfRotate(): void
    {
        $this->makeClient()->request('GET', self::URI, ['name' => 'rotate']);
        self::assertResponseIsSuccessful();
    }

    public function testPdfGeometry(): void
    {
        $this->makeClient()->request('GET', self::URI, ['name' => 'geometry']);
        self::assertResponseIsSuccessful();
    }

    public function testPdfEtiquettes(): void
    {
        $this->makeClient()->request('GET', self::URI, ['name' => 'etiquette']);
        self::assertResponseIsSuccessful();
    }
}
