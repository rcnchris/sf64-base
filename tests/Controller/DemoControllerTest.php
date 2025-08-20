<?php

namespace App\Tests\Controller;

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
}
