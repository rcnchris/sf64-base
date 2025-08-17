<?php

namespace App\Controller;

use App\Entity\Tablette;
use App\Form\TabletteType;
use App\Repository\TabletteRepository;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/tablette', name: 'tablette.')]
final class TabletteController extends AppAbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(TabletteRepository $tabletteRepository, Request $request): Response
    {
        $this->addLog('Tablettes', [
            'action' => __FUNCTION__,
            'entity' => 'Tablette'
        ]);
        return $this->render('tablette/list.html.twig', [
            'title' => $this->trans('entity.tablette', ['tablettes' => 2]),
            'tablettes' => $this->paginate($tabletteRepository->findListQb()->where('t.lvl = 0'), $request),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, TabletteRepository $tabletteRepository): Response
    {
        $tablette = new Tablette();
        $form = $this->createForm(TabletteType::class, $tablette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tabletteRepository->save($tablette);
            $this->addFlash('toast-success', $this->trans('toast.' . __FUNCTION__));
            return $this->redirectToRoute('tablette.list', [], Response::HTTP_SEE_OTHER);
        }
        $title = $this->trans('new') . ' une tablette';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Tablette'
        ]);
        return $this->render('tablette/edit.html.twig', [
            'title' => $title,
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
    public function edit(string $slug, Request $request, Tablette $tablette, TabletteRepository $tabletteRepository): Response
    {
        if ($slug !== $tablette->getSlug()) {
            return $this->redirectToRoute(
                'tablette.edit',
                ['id' => $tablette->getId(), 'slug' => $tablette->getSlug()],
                Response::HTTP_SEE_OTHER
            );
        }

        $form = $this->createForm(TabletteType::class, $tablette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tabletteRepository->save($tablette);
            $this->addFlash('toast-success', $this->trans('toast.' . __FUNCTION__));

            return $this->redirectToRoute(
                'tablette.edit',
                ['id' => $tablette->getId(), 'slug' => $tablette->getSlug()],
                Response::HTTP_SEE_OTHER
            );
        }
        $title = $this->trans(__FUNCTION__) . ' une tablette';
        $this->addLog($title, [
            'action' => 'Editer une tablette',
            'entity' => 'Tablette',
            'entity_id' => $tablette->getId()
        ]);
        return $this->render('tablette/edit.html.twig', [
            'title' => $title,
            'tablette' => $tablette,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Tablette $tablette, TabletteRepository $tabletteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tablette->getId(), $request->getPayload()->getString('_token'))) {
            $tabletteRepository->remove($tablette);
            $this->addFlash('toast-success', $this->trans('toast.' . __FUNCTION__));
        }
        return $this->redirectToRoute('tablette.list', [], Response::HTTP_SEE_OTHER);
    }
}
