<?php

namespace App\Tests\Service;

use App\Pdf\AppPdf;
use App\Service\PdfService;
use App\Tests\AppKernelTestCase;

final class PdfServiceTest extends AppKernelTestCase
{
    private ?PdfService $service = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = static::getContainer()->get(PdfService::class);
    }

    protected function tearDown(): void
    {
        $this->service = null;
        parent::tearDown();
    }

    private function getPdfFilename(string $method): string
    {
        return $this->getRootDir(sprintf('/tests/files/%s.pdf', $method));
    }

    public function testMakeReturnMcvPdfInstance(): void
    {
        self::assertInstanceOf(AppPdf::class, $this->service->make());
    }

    public function testMakeWithCreateLogoFile(): void
    {
        $filename = $this->getRootDir('/assets/images/logo-pdf.png');
        if (file_exists($filename)) {
            unlink($filename);
        }
        self::assertInstanceOf(AppPdf::class, $this->service->make());
    }

    public function testUseTraitsInInformations(): void
    {
        $pdf = $this->service->make([
            'title' => __FUNCTION__,
            'subject' => __CLASS__,
        ])->printInfos();
        self::assertIsString($pdf->render('F', $this->getPdfFilename(__FUNCTION__)));
    }

    public function testGetInfosWithNotFoundJoinedFile(): void
    {
        $pdf = $this->service->make([
            'title' => __FUNCTION__,
            'subject' => __CLASS__,
        ]);
        $this->expectException(\Exception::class);
        $pdf->addFile('/fake/file.txt');
    }

    public function testAddTocWithoutBookmark(): void
    {
        self::assertInstanceOf(
            AppPdf::class,
            $this->service->make()->addToc(__FUNCTION__)
        );
    }

    public function testAddTocWithBookmarksIndent(): void
    {
        self::assertIsString(
            $this->service
                ->make()
                ->addBookmark(basename(__FILE__))
                ->addBookmark(__FUNCTION__, 1)
                ->addBookmark(__CLASS__)
                ->addToc()
                ->render('S')
        );
    }

    public function testAddTocWithTooLongBookmark(): void
    {
        self::assertInstanceOf(
            AppPdf::class,
            $this->service->make()->addBookmark($this->getFaker()->realText())->addToc()
        );
    }

    public function testAddNoUtf8Bookmark(): void
    {
        self::assertInstanceOf(
            AppPdf::class,
            $this->service->make()->addBookmark(__FUNCTION__, 0, 0, false)
        );
    }

    public function testAddBookmarkWithNegativeY(): void
    {
        self::assertInstanceOf(
            AppPdf::class,
            $this->service->make()->addBookmark(__FUNCTION__, 0, -1, false)
        );
    }

    public function testRenderWithBookmarks(): void
    {
        self::assertIsString(
            $this->service
                ->make()
                ->addBookmark(__FUNCTION__)
                ->printInfos()
                ->render('S')
        );
    }

    public function testRenderWithJoinedFile(): void
    {
        self::assertIsString(
            $this->service
                ->make(['open_attachment_pane' => true])
                ->addFile(__FILE__, __FUNCTION__, __FUNCTION__)
                ->printInfos()
                ->render('S')
        );
    }
}
