<?php

namespace App\Controller;

use App\Pdf\EtiquettePdf;
use App\Service\PdfService;
use App\Utils\Tools;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demo', name: 'demo.', methods: ['GET'])]
final class DemoController extends AppAbstractController
{
    #[Route('/pdf/app', name: 'pdf.app')]
    public function appPdf(PdfService $pdfService): Response
    {
        $title = 'Démo AppPdf';
        $pdf = $pdfService
            ->make([
                'title' => $title,
                'subject' => 'PDF de l\'application',
            ])
            ->printInfos();

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/bookmark', name: 'pdf.bookmark')]
    public function bookmarkPdf(PdfService $pdfService): Response
    {
        $title = 'Signets et TOC';
        $pdf = $pdfService
            ->make(['title' => $title])
            ->addBookmark($title, 0, 1)
            ->addBookmark('Texte', 1)
            ->print($this->getFaker()->realText(1000), align: 'J');

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/file', name: 'pdf.file')]
    public function filePdf(PdfService $pdfService): Response
    {
        $title = 'Fichier attaché';
        $pdf = $pdfService
            ->make(['title' => $title])
            ->addBookmark($title, 0, 1)
            ->addFile(__FILE__, desc: 'Controller');

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(false, true)
            ->addToc()
            ->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/rotate', name: 'pdf.rotate')]
    public function rotatePdf(PdfService $pdfService): Response
    {
        $title = 'Rotations';
        $pdf = $pdfService
            ->make([
                'title' => $title,
                'watermark' => $title,
            ])
            ->addBookmark($title, 0, 1)
            ->rotatedText('Oyé les gens', 45, 50, 50)
            ->rotatedImage(sprintf('%s/images/empty.jpg', $this->getParameter('app.assets_dir')), 45, 70, 100, 20, 20);

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/geometry', name: 'pdf.geometry')]
    public function geometryPdf(PdfService $pdfService): Response
    {
        $title = 'Géométrie';
        $pdf = $pdfService
            ->make([
                'title' => $title,
                'graduated_grid' => true,
            ])
            ->addBookmark($title, 0, 1);

        $yLabel = $pdf->getStartContentY();
        $pdf
            ->addBookmark('Cercles', 1, $yLabel)
            ->setFontStyle(style: 'B', size: 12)
            ->print('Cercles', border: 'B')
            ->circle(15, $yLabel + 15, 5)
            ->circle(30, $yLabel + 15, 5, 'F')
            ->circle(45, $yLabel + 15, 5, 'FD');

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Ellipse', 1)
            ->print('Ellipse', border: 'B')
            ->ellipsis(20, $yLabel + 15, 10, 5);

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Secteur', 1)
            ->print('Secteur', border: 'B')
            ->sector(15, $yLabel + 15, 5, 0, 90)
            ->sector(15, $yLabel + 15, 5, 90, 180)
            ->sector(15, $yLabel + 15, 5, 180, 270)
            ->sector(15, $yLabel + 15, 5, 270, 0);

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Rectangles arrondis', 1)
            ->print('Rectangles arrondis', border: 'B')
            ->roundedRect(20, 10, 5, '1234', 10, $yLabel + 10)
            ->roundedRect(20, 10, 5, '134', 35, $yLabel + 10)
            ->roundedRect(20, 10, 5, '14', 60, $yLabel + 10)
            ->roundedRect(20, 10, 5, '1', 85, $yLabel + 10)
            ->roundedRect(20, 10, 5, '2', 110, $yLabel + 10);

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Polygone', 1)
            ->print('Polygone', border: 'B')
            ->polygon([10, $yLabel + 20, 20, $yLabel + 20, 15, $yLabel + 10, 10, $yLabel + 20]);

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/etiquette', name: 'pdf.etiquette')]
    public function etiquettePdf(): Response
    {
        $faker = $this->getFaker();
        $title = 'Etiquette';
        $pdf = new EtiquettePdf('avery.L7163');
        for ($i = 1; $i <= 20; $i++) {
            $text = sprintf(
                "%s\n%s\n%s %s,\n%s",
                sprintf('%s %s', $faker->firstName(), $faker->lastName()),
                $faker->streetAddress(),
                $faker->postcode(),
                $faker->city(),
                $faker->country(),
            );
            $pdf->addEtiquette($text);
        }
        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/autoprint', name: 'pdf.autoprint')]
    public function autoprintPdf(PdfService $pdfService): Response
    {
        $title = 'AutoPrint';
        $pdf = $pdfService
            ->make(compact('title'))
            ->addBookmark($title, 0, 1)
            ->autoPrint();

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(false, true)
            ->addToc()
            ->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/charts', name: 'pdf.charts')]
    public function charts(PdfService $pdfService): Response
    {
        $title = 'Graphiques';
        $faker = $this->getFaker();
        $countries = [];
        $colors = [];
        for ($i = 0; $i < 8; $i++) {
            $countries[$faker->country()] = mt_rand(800, 50000);
            $colors[] = Tools::getRandColor();
        }
        $pdf = $pdfService
            ->make([
                'title' => $title,
                'graduated_grid' => true,
            ])
            ->addBookmark($title, 0, 1);

        $label = 'Camembert des pays en valeur sans couleur';
        $pdf
            ->addBookmark($label, 1)
            ->setFontStyle(style: 'B', size: 12)
            ->print($label, border: 'B')
            ->chartPie(
                x: 30,
                y: 60,
                r: 20,
                data: $countries,
                format: '%l (%v)',
                decimals: 0,
            );

        $label = 'Camembert des pays en pourcentage avec couleurs';
        $pdf
            ->setCursor(10, 95)
            ->addBookmark($label, 1)
            ->setFontStyle(style: 'B', size: 12)
            ->print($label, border: 'B')
            ->chartPie(
                x: 30,
                y: 125,
                r: 20,
                data: $countries,
                format: '%l (%p)',
                colors: $colors,
                decimals: 0,
            );

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }
}
