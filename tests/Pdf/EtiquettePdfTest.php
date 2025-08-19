<?php

namespace App\Tests\Pdf;

use App\Pdf\EtiquettePdf;
use App\Tests\AppTestCase;
use App\Utils\Collection;

class EtiquettePdfTest extends AppTestCase
{
    public function testInstanceWithAveryFormat(): void
    {
        self::assertInstanceOf(EtiquettePdf::class, new EtiquettePdf('avery.L7163'));
    }

    public function testInstanceWithDefinedFormat(): void
    {
        self::assertInstanceOf(EtiquettePdf::class, new EtiquettePdf('avery.L7163'));
    }

    public function testInstanceWithInchUnitFormat(): void
    {
        self::assertInstanceOf(EtiquettePdf::class, new EtiquettePdf('avery.5164'));
    }

    public function testInstanceWithUndefinedFormatReturnException(): void
    {
        $this->expectException(\Exception::class);
        new EtiquettePdf('agipa.9999');
    }

    public function testInstanceWithFormatDefinition(): void
    {
        $pdf = new EtiquettePdf([
            'size' => 'A4',
            'unit' => 'mm',
            'margin_left' => 10,
            'margin_top' => 10,
            'nx' => 3,
            'ny' => 8,
            'space_x' => 0,
            'space_y' => 0,
            'width' => 0,
            'height' => 0,
            'font-size' => 8
        ]);
        self::assertInstanceOf(Collection::class, $pdf->getEtiqFormats());
    }

    public function testGetFormatsReturnCollection(): void
    {
        $pdf = new EtiquettePdf('avery.L7163');
        self::assertInstanceOf(Collection::class, $pdf->getEtiqFormats());
    }

    public function testInstanceWithInvalidFontSize(): void
    {
        $this->expectException(\Exception::class);
        new EtiquettePdf(['font-size' => 24]);
    }
}
