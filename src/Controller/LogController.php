<?php

namespace App\Controller;

use App\Entity\Log;
use App\Form\LogSearchForm;
use App\Model\LogSearchModel;
use App\Repository\LogRepository;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/log', name: 'log.')]
final class LogController extends AppAbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(LogRepository $logRepository, Request $request): Response
    {
        $search = new LogSearchModel();
        $form = $this->createForm(LogSearchForm::class, $search);
        $form->handleRequest($request);

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

    #[Route('/show/{id}', name: 'show', methods: ['GET'])]
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
}
