<?php

namespace App\Tests\Controller;

use App\Entity\Log;
use App\Tests\AppWebTestCase;

final class DemoControllerTest extends AppWebTestCase
{
    public function testPdfApp(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/app');
    }

    public function testPdfBookmark(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/bookmark');
    }

    public function testPdfFile(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/file');
    }

    public function testPdfRotate(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/rotate');
    }

    public function testPdfGeometry(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/geometry');
    }

    public function testPdfEtiquette(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/etiquette');
    }

    public function testPdfAutoprint(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/autoprint');
    }

    public function testPdfCharts(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/charts');
    }

    public function testBarCodes(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/barcodes');
    }

    public function testFonts(): void
    {
        $this->assertRequestIsSuccessful('/demo/pdf/fonts');
    }

    public function testForm(): void
    {
        $client = $this->makeClient();
        $uri = '/demo/form';
        $client->request('GET', $uri);
        self::assertResponseIsSuccessful();
        $client->submitForm('Feu !', [
            'daterange' => '22/08/2025 00:00 - 22/08/2025 23:59',
        ]);
        self::assertResponseRedirects($uri);
    }

    public function testTwig(): void
    {
        $this->assertRequestIsSuccessful('/demo/twig');
    }

    public function testCalendar(): void
    {
        $this->assertRequestIsSuccessful('/demo/calendar');
    }

    public function testCalendarEdit(): void
    {
        $client = $this->makeClient();
        $log = $this->getRepository(Log::class)->findRand('l');
        $client->jsonRequest('PUT', sprintf('/demo/calendar/edit/%d', $log->getId()), [
            'title' => __FUNCTION__,
            'start' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'end' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'allDay' => false
        ]);
        self::assertResponseStatusCodeSame(200);
    }

    public function testCalendarEditWithoutStart(): void
    {
        $client = $this->makeClient();
        $log = $this->getRepository(Log::class)->findRand('l');
        $client->jsonRequest('PUT', sprintf('/demo/calendar/edit/%d', $log->getId()), [
            'title' => __FUNCTION__,
            'end' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'allDay' => false
        ]);
        self::assertResponseStatusCodeSame(400);
    }
}
