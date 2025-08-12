<?php

namespace App\Controller;

use App\Entity\Tablette;
use App\Form\TabletteType;
use App\Repository\TabletteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/tablette', name: 'tablette.')]
final class TabletteController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(TabletteRepository $tabletteRepository, TranslatorInterface $translator): Response
    {
        return $this->render('tablette/list.html.twig', [
            'title' => $translator->trans('entity.tablette', ['tablettes' => 2]),
            'tablettes' => $tabletteRepository->findBy(['lvl' => 0]),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tablette = new Tablette();
        $form = $this->createForm(TabletteType::class, $tablette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tablette);
            $entityManager->flush();

            return $this->redirectToRoute('tablette.list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tablette/edit.html.twig', [
            'title' => sprintf('%s tablette', __FUNCTION__),
            'tablette' => $tablette,
            'form' => $form,
        ]);
    }

    #[Route(
        '/edit/{id}/{slug}',
        name: 'edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => Requirement::DIGITS, 'slug' => '[a-z0-9\-]+']
    )]
    public function edit(Request $request, Tablette $tablette, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TabletteType::class, $tablette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                'tablette.edit',
                ['id' => $tablette->getId(), 'slug' => $tablette->getSlug()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('tablette/edit.html.twig', [
            'title' => sprintf('%s tablette', __FUNCTION__),
            'tablette' => $tablette,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Tablette $tablette, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tablette->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tablette);
            $entityManager->flush();
        }
        return $this->redirectToRoute('tablette.list', [], Response::HTTP_SEE_OTHER);
    }
}
