<?php

namespace App\Controller;

use App\Entity\Log;
use App\Form\DemoType;
use App\Pdf\{DumpFontsPdf, EtiquettePdf};
use App\Repository\LogRepository;
use App\Service\PdfService;
use App\Utils\Tools;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

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
        $this->addLog($title, ['action' => 'pdf']);
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

        $this->addLog($title, ['action' => 'pdf']);
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

        $this->addLog($title, ['action' => 'pdf']);
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

        $this->addLog($title, ['action' => 'pdf']);
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
            ->addBookmark('Rectangle', 1)
            ->print('Rectangle', border: 'B')
            ->rectangle(y: $yLabel + 10, bgColor: '#f39c12');

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Rectangles arrondis', 1)
            ->print('Rectangles arrondis', border: 'B')
            ->roundedRectangle(20, 10, 5, '1234', 10, $yLabel + 10)
            ->roundedRectangle(20, 10, 5, '134', 35, $yLabel + 10)
            ->roundedRectangle(20, 10, 5, '14', 60, $yLabel + 10)
            ->roundedRectangle(20, 10, 5, '1', 85, $yLabel + 10)
            ->roundedRectangle(20, 10, 5, '2', 110, $yLabel + 10);

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Polygone', 1)
            ->print('Polygone', border: 'B')
            ->polygon([10, $yLabel + 20, 20, $yLabel + 20, 15, $yLabel + 10, 10, $yLabel + 20]);

        $yLabel = $yLabel + 25;
        $pdf
            ->setCursor(10, $yLabel)
            ->addBookmark('Etoile', 1)
            ->print('Etoile', border: 'B')
            ->star(5, 10, 20, 60, $yLabel + 25);

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);

        $this->addLog($title, ['action' => 'pdf']);
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

        $this->addLog($title, ['action' => 'pdf']);
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

        $this->addLog($title, ['action' => 'pdf']);
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
            ->make(compact('title'))
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

        $label = 'Histogramme des pays';
        $pdf
            ->setCursor(10, 160)
            ->addBookmark($label, 1)
            ->setFontStyle(style: 'B', size: 12)
            ->print($label, border: 'B')
            ->chartBar(
                data: $countries,
                barColor: '#1abc9c',
                decimals: 0,
                nbScales: 5
            );

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);

        $this->addLog($title, ['action' => 'pdf']);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/barcodes', name: 'pdf.barcodes')]
    public function barcodes(PdfService $pdfService): Response
    {
        $title = 'Codes à barres';
        $faker = $this->getFaker();
        $pdf = $pdfService
            ->make([
                'title' => $title,
                'graduated_grid' => true,
            ])
            ->addBookmark($title, 0, 1);

        $label = 'EAN 13';
        $pdf
            ->addBookmark($label, 1)
            ->setFontStyle(style: 'B', size: 12)
            ->print($label, border: 'B')
            ->barCodeEan13($faker->ean13(), y: 40);

        $label = 'UPCA';
        $pdf
            ->setCursor(10, 80)
            ->addBookmark($label, 1)
            ->setFontStyle(style: 'B', size: 12)
            ->print($label, border: 'B')
            ->barCodeUpca('306532893210', y: 90);

        $label = 'Code 39';
        $pdf
            ->setCursor(10, 120)
            ->addBookmark($label, 1)
            ->setFontStyle(style: 'B', size: 12)
            ->print($label, border: 'B')
            ->barCodeCode39($faker->ean8(), y: 130);

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);

        $this->addLog($title, ['action' => 'pdf']);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/pdf/fonts', name: 'pdf.fonts')]
    public function fonts(PdfService $pdfService): Response
    {
        $title = 'Polices';
        $pdf = new DumpFontsPdf(compact('title'));
        $pdf->addBookmark($title, 0, 1);

        $pdf
            ->dumpFont('Courier', false)
            ->dumpFont('Arial')
            ->dumpFont('Helvetica')
            ->dumpFont('Times')
            ->dumpFont('Symbol')
            ->dumpFont('Zapfdingbats');

        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), __FUNCTION__);
        $pdf
            ->printInfos(true, true)
            ->addToc()
            ->render('F', $filename);

        $this->addLog($title, ['action' => 'pdf']);
        return $this->render('demo/pdf.html.twig', [
            'title' => $title,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }

    #[Route('/form', name: 'form', methods: ['GET', 'POST'])]
    public function form(Request $request): Response
    {
        $title = 'Formulaire';
        $form = $this->createForm(DemoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('demo.form');
        }

        $this->addLog($title, ['action' => 'pdf']);
        return $this->render('demo/form.html.twig', [
            'title' => $title,
            'form' => $form,
        ]);
    }

    #[Route('/twig', name: 'twig')]
    public function twig(): Response
    {
        $title = 'Twig';
        $faker = $this->getFaker();

        $this->addLog($title, ['action' => 'pdf']);
        return $this->render('demo/twig.html.twig', [
            'title' => $title,
            'faker' => $faker,
            'file' => __FILE__,
        ]);
    }

    #[Route('/calendar', name: 'calendar')]
    public function calendar(LogRepository $logRepository, RouterInterface $router): Response
    {
        $title = 'Calendrier';
        $this->addLog($title, ['action' => 'show']);
        return $this->render('demo/calendar.html.twig', [
            'title' => $title,
            'events' => json_encode($logRepository->findForCalendar($router))
        ]);
    }

    #[Route('/calendar/edit/{id}', name: 'calendar.edit', methods: ['PUT'])]
    public function calendarEdit(?Log $log, LogRepository $logRepository, Request $request): Response
    {
        $data = json_decode($request->getContent());
        if (empty($data) || !isset($data->start)) {
            return new Response('Aucune donnée', 400);
        }

        $logRepository->save($log->setCreatedAt(new \DateTimeImmutable($data->start)));
        $this->addLog('Calendrier Edit', [
            'action' => 'edit',
            'entity' => 'Log',
            'entity_id' => $log->getId(),
            'data' => $data,
        ]);
        return new Response('Enregistrement modifié', 200);
    }
}
