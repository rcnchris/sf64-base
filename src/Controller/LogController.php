<?php

namespace App\Controller;

use App\Entity\Log;
use App\Repository\LogRepository;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/log', name: 'log.')]
final class LogController extends AppAbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(LogRepository $logRepository, Request $request): Response
    {
        $title = 'Liste logs';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
        ]);
        return $this->render('log/list.html.twig', [
            'title' => $title,
            'logs' => $this->paginate($logRepository->findBy([], ['createdAt' => 'DESC']), $request),
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
