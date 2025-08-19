<?php

namespace App\Controller;

use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demo', name: 'demo.')]
final class DemoController extends AbstractController
{
    #[Route('/pdf', name: 'pdf')]
    public function pdf(Request $request, PdfService $pdfService): Response
    {
        $name = $request->query->get('name', 'app-pdf');
        $filename = sprintf('%s/%s.pdf', $this->getParameter('app.docs_dir'), $name);
        // $faker = Factory::create($this->getParameter('app.locale_country'));
        switch ($name) {
            case 'app-pdf':
            default:
                $pdf = $pdfService
                    ->make([
                        'title' => 'Démo AppPdf',
                        'subject' => 'PDF de l\'application'
                    ])
                    ->printInfos(false);

                $pdf->render('F', $filename);
                break;

            case 'bookmark':
                $pdf = $pdfService
                    ->make(['title' => 'Signets et TOC'])
                    ->addBookmark('Signets et TOC')
                    ->addToc();
                $pdf->render('F', $filename);
                break;

            case 'file':
                $pdf = $pdfService
                    ->make(['title' => 'Fichier attaché'])
                    ->addFile(__FILE__);
                $pdf->render('F', $filename);
                break;

            case 'rotate':
                $pdf = $pdfService
                    ->make(['title' => 'Rotations'])
                    ->rotatedText('Oyé les gens', 45, 50, 50)
                    ->rotatedImage(sprintf('%s/images/empty.jpg', $this->getParameter('app.assets_dir')), 45, 70, 100, 20, 20);
                $pdf->render('F', $filename);
                break;

            case 'geometry':
                $pdf = $pdfService
                    ->make([
                        'title' => 'Géométrie',
                        'graduated_grid' => true,
                    ])
                    ->circle(20, 50, 10)
                    ->circle(45, 50, 10, 'F')
                    ->circle(70, 50, 10, 'FD')
                    ->ellipsis(30, 80, 20, 10)
                    ->roundedRect(20, 10, 5, '1234', 10, 100)
                    ->roundedRect(20, 10, 5, '134', 35, 100)
                    ->roundedRect(20, 10, 5, '14', 60, 100)
                    ->roundedRect(20, 10, 5, '1', 85, 100)
                    ->roundedRect(20, 10, 5, '2', 110, 100);
                $pdf->render('F', $filename);
                break;
        }
        return $this->render('demo/pdf.html.twig', [
            'title' => 'Démo PDF',
            'name' => $name,
            'pdf' => $pdf,
            'filename' => $filename,
        ]);
    }
}
