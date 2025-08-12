<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app.', methods: ['GET'])]
final class HomeController extends AppAbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app.home', [], Response::HTTP_FOUND);
    }

    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('home/home.html.twig', [
            'title' => __FUNCTION__,
        ]);
    }

    #[Route('/readme', name: 'readme')]
    public function readme(): Response
    {
        return $this->render('home/readme.html.twig', [
            'title' => __FUNCTION__,
            'version' => json_decode($this->getFileContent('composer.json'), true)['version'],
            'install_code' => [
                'git clone https://github.com/rcnchris/sf64-base.git my-project-dir',
                'cd my-project-dir',
                'composer app-install'
            ],
            'update_code' => [
                'composer app-update'
            ],
        ]);
    }
}
