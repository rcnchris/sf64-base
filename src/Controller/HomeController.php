<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app.', methods: ['GET'])]
final class HomeController extends AbstractController
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
}
