<?php

namespace App\Tests\Service;

use App\Service\ChartJsService;
use App\Tests\AppKernelTestCase;
use Symfony\UX\Chartjs\Model\Chart;

final class ChartJsServiceTest extends AppKernelTestCase
{
    private ?ChartJsService $service = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = static::getContainer()->get(ChartJsService::class);
    }

    protected function tearDown(): void
    {
        $this->service = null;
        parent::tearDown();
    }

    public function testMakeWithoutTypeReturnChartInstanceWithBarType(): void
    {
        $chart = $this->service->make();
        self::assertInstanceOf(Chart::class, $chart);
        self::assertEquals('bar', $chart->getType());
    }

    public function testMakeWithInvalidTypeReturnChartInstanceWithBarType(): void
    {
        $chart = $this->service->make('fake');
        self::assertInstanceOf(Chart::class, $chart);
        self::assertEquals('bar', $chart->getType());
    }
}
