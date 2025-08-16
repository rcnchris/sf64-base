<?php

namespace App\Tests\Pdf;

use App\Pdf\MyFPDF;
use App\Tests\AppTestCase;
use App\Utils\{Collection, Tools};

class MyFpdfTest extends AppTestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(MyFPDF::class, new MyFPDF());
    }

    public function testGetOptionsReturnCollection(): void
    {
        self::assertInstanceOf(Collection::class, (new MyFPDF())->getOptions());
    }

    public function testGetDatasReturnCollection(): void
    {
        self::assertInstanceOf(Collection::class, (new MyFPDF())->getData());
    }

    public function testGetMetasReturnCollection(): void
    {
        self::assertInstanceOf(Collection::class, (new MyFPDF())->getMetas());
    }

    public function testGetInfosReturnCollection(): void
    {
        self::assertInstanceOf(Collection::class, (new MyFPDF())->getInfos());
    }

    public function testGetMarginsReturnCollection(): void
    {
        $pdf = new MyFPDF();

        $margins = $pdf->getMargins();
        self::assertInstanceOf(Collection::class, $margins);
        self::assertFalse($margins->isEmpty());

        self::assertArrayHasKey('left', $margins->toArray());
        self::assertEquals(10.0, $margins->left);

        self::assertArrayHasKey('right', $margins->toArray());
        self::assertEquals(10.0, $margins->right);

        self::assertArrayHasKey('top', $margins->toArray());
        self::assertEquals(10.0, $margins->top);

        self::assertArrayHasKey('bottom', $margins->toArray());
        self::assertEquals(10.0, $margins->top);

        self::assertArrayHasKey('cell', $margins->toArray());
        self::assertEquals(1.0, $margins->cell);
    }

    public function testNewPage(): void
    {
        $pdf = new MyFPDF();
        self::assertEquals(1, $pdf->getTotalPages());
        self::assertEquals(2, $pdf->newPage()->getTotalPages());
    }

    public function testAddLnReturnInstance(): void
    {
        self::assertInstanceOf(MyFPDF::class, (new MyFPDF())->addLn());
    }

    public function testSetToolColorFill(): void
    {
        self::assertInstanceOf(MyFPDF::class, (new MyFPDF())->setToolColor('fill'));
    }

    public function testSetToolColorWithInvalidToolNameReturnException(): void
    {
        $this->expectException(\Exception::class);
        (new MyFPDF())->setToolColor('fake');
    }

    public function testSetMarginWithInvalidTypeReturnException(): void
    {
        $this->expectException(\Exception::class);
        (new MyFPDF())->setMargin('fake', 0);
    }

    public function testGetCreatedDateWhenNotRenderedReturnNull(): void
    {
        self::assertNull((new MyFPDF())->getCreatedAt());
    }

    public function testGetCreatedDateWhenRenderedReturnDateTimeImmutable(): void
    {
        $pdf = new MyFPDF();
        $pdf->printInfos()->render('S');
        self::assertInstanceOf(\DateTimeImmutable::class, $pdf->getCreatedAt());
    }

    public function testGetBodySizes(): void
    {
        $pdf = new MyFPDF();
        self::assertIsFloat($pdf->getBodyWidth());
        self::assertIsFloat($pdf->getBodyHeight());
    }

    public function testGetMiddleOf(): void
    {
        $pdf = new MyFPDF();
        self::assertIsFloat($pdf->getMiddleOf('x'));
        self::assertIsFloat($pdf->getMiddleOf('y'));

        $this->expectException(\Exception::class);
        $pdf->getMiddleOf('z');
    }

    public function testNewPageWithY(): void
    {
        $pdf = new MyFPDF();
        self::assertEquals(1, $pdf->getTotalPages());
        self::assertInstanceOf(MyFPDF::class, $pdf->newPage(y: 50));
        self::assertEquals(2, $pdf->getTotalPages());
    }

    public function testConvertTextWithEmptyContentReturnEmptyString(): void
    {
        self::assertSame('', (new MyFPDF())->convertText());
    }

    public function testPrintTextWithTextMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print($this->getFaker()->sentences(3, true), mode: 'text')
                ->render('S')
        );
    }

    public function testPrintWithObjectMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print(new Collection($this->getFaker()->creditCardDetails()), mode: 'text')
                ->render('S')
        );
    }

    public function testPrintWithArrayMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print($this->getFaker()->creditCardDetails(), mode: 'text')
                ->render('S')
        );
    }

    public function testPrintTextWithCellMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print($this->getFaker()->sentences(3, true), mode: 'cell')
                ->render('S')
        );
    }

    public function testPrintObjectWithCellMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print(new Collection($this->getFaker()->creditCardDetails()), mode: 'cell')
                ->render('S')
        );
    }

    public function testPrintArrayWithCellMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print($this->getFaker()->creditCardDetails(), mode: 'cell')
                ->render('S')
        );
    }

    public function testPrintTextWithMultiMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print($this->getFaker()->sentences(3, true), mode: 'multi')
                ->render('S')
        );
    }

    public function testPrintObjectWithMultiMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print(new Collection($this->getFaker()->creditCardDetails()), mode: 'multi')
                ->render('S')
        );
    }

    public function testPrintArrayAssociativeWithMultiMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print($this->getFaker()->creditCardDetails(), mode: 'multi')
                ->render('S')
        );
    }

    public function testPrintArrayListWithMultiMode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print(['ola', 'ole', 'oli'], mode: 'multi')
                ->render('S')
        );
    }

    public function testPrintWithInvalidMode(): void
    {
        $this->expectException(\Exception::class);
        (new MyFPDF())->print('ola les gens', mode: 'fake');
    }

    public function testPrintCode(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->printCode("self::assertSame('', (new MyFPDF())->printCode());")
                ->render('S')
        );
    }

    public function testWithCustomPagination(): void
    {
        self::assertIsString((new MyFPDF([
            'pagination_enabled' => true,
            'pagination_fill' => '#f39c12',
        ]))->print('ola')->render('S'));

        self::assertIsString((new MyFPDF([
            'pagination_enabled' => true,
            'pagination_fill' => true,
        ]))->print('ola')->render('S'));
    }

    public function testGraduatedGrid(): void
    {
        self::assertIsString(
            (new MyFPDF(['graduated_grid' => true]))
                ->printInfos()
                ->render('S')
        );
        self::assertIsString(
            (new MyFPDF(['graduated_grid' => 10]))
                ->printInfos()
                ->render('S')
        );
    }

    public function testRenderInNewDir(): void
    {
        $newDir = $this->getRootDir(sprintf('/tests/files/%s', __FUNCTION__));
        if (is_dir($newDir)) {
            Tools::removeDirTree($newDir);
        }
        $filename = sprintf('%s/%s.pdf', $newDir, __FUNCTION__);
        self::assertIsString((new MyFPDF())->printInfos()->render('F', $filename));
    }

    public function testPaginationInHeader(): void
    {
        self::assertIsString(
            (new MyFPDF([
                'header_height' => 10,
                'pagination_enabled' => 'header',
                'pagination_fill' => true,
            ]))
                ->printInfos()
                ->render('S')
        );

        self::assertIsString(
            (new MyFPDF([
                'header_height' => 10,
                'pagination_enabled' => 'header',
                'pagination_fill' => '#27ae60',
            ]))
                ->printInfos()
                ->render('S')
        );
    }

    public function testPaginationInFooter(): void
    {
        self::assertIsString(
            (new MyFPDF([
                'footer_height' => 10,
                'pagination_enabled' => 'footer',
                'pagination_fill' => true,
            ]))
                ->printInfos()
                ->render('S')
        );

        $filename = sprintf('%s/%s.pdf', $this->getRootDir('/tests/files'), __FUNCTION__);
        $pdf = new MyFPDF([
            'footer_height' => 10,
            'pagination_enabled' => 'footer',
            'pagination_fill' => '#27ae60',
        ]);
        self::assertIsString($pdf->printInfos()->render('F', $filename));
    }

    public function testDrawLine(): void
    {
        self::assertIsString(
            (new MyFPDF())
                ->print('Ola les gens')
                ->drawLine()
                ->render('S')
        );
    }
}
