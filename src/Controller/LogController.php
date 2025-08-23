<?php

namespace App\Controller;

use App\Entity\Log;
use App\Form\LogSearchForm;
use App\Model\LogSearchModel;
use App\Repository\LogRepository;
use App\Service\ChartJsService;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/log', name: 'log.')]
#[IsGranted('ROLE_APP')]
final class LogController extends AppAbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(LogRepository $logRepository, Request $request): Response
    {
        $search = new LogSearchModel();
        $form = $this->createForm(LogSearchForm::class, $search);
        $form->handleRequest($request);
        dump($form->getData());

        $title = 'Liste logs';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
        ]);
        return $this->render('log/list.html.twig', [
            'title' => $title,
            'logs' => $this->paginate($logRepository->findListQb($search), $request),
            'search' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(Log $log): Response
    {
        $title = 'Voir log';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
            'entity_id' => $log->getId(),
        ]);
        return $this->render('log/show.html.twig', [
            'title' => $title,
            'log' => $log,
        ]);
    }

    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        $title = 'Calendrier logs';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
        ]);
        return $this->render('log/calendar.html.twig', [
            'title' => $title,
        ]);
    }

    #[Route('/pivottable', name: 'pivottable', methods: ['GET'])]
    public function pivottable(LogRepository $logRepository, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('log.list', [], Response::HTTP_SEE_OTHER);
        }
        $this->addLog(ucfirst(__FUNCTION__), [
            'action' => __FUNCTION__,
            'entity' => 'Log'
        ]);
        $data = [
            'rows' => ['Annee', 'Mois'],
            'cols' => ['Jour'],
            'aggregate' => ['cnt'],
            'items' => $logRepository->countByAllTime(),
        ];
        return $this->json($data);
    }

    #[Route('/chart', name: 'chart', methods: ['GET'])]
    public function chart(
        LogRepository $logRepository,
        ChartJsService $chartJsService
    ): Response {
        $countHours = $logRepository->countByHour();
        $chartHours = $chartJsService
            ->make('line', ['height' => 70])
            ->setData([
                'labels' => array_column($countHours, 'hour'),
                'datasets' => [
                    [
                        'label' => 'Actions par heures',
                        'borderColor' => '#0dcaf0',
                        'backgroundColor' => '#1abc9c',
                        'tension' => .3,
                        'data' => array_column($countHours, 'cnt')
                    ]
                ]
            ]);

        $countHours = $logRepository->countByDays();
        $chartDays = $chartJsService
            ->make('bar', ['height' => 70])
            ->setData([
                'labels' => array_column($countHours, 'day'),
                'datasets' => [
                    [
                        'label' => 'Actions par jours de la semaine',
                        'borderColor' => '#0dcaf0',
                        'backgroundColor' => '#1abc9c',
                        'tension' => .3,
                        'data' => array_column($countHours, 'cnt')
                    ]
                ]
            ]);
        $title = 'Graphiques logs';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log'
        ]);
        return $this->render('log/chart.html.twig', [
            'title' => $title,
            'chart_hours' => $chartHours,
            'chart_days' => $chartDays,
        ]);
    }
}
