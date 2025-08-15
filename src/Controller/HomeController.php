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
        $this->addLog(ucfirst($this->trans(__FUNCTION__)), ['action' => 'show']);
        return $this->render('home/home.html.twig', [
            'title' => __FUNCTION__,
        ]);
    }

    #[Route('/readme', name: 'readme')]
    public function readme(): Response
    {
        $composer = json_decode($this->getFileContent('composer.json'), true);
        $readme = [
            sprintf("## %s\n", $composer['description']),
            sprintf("Version : **%s**\n", $composer['version']),
            "### Installation\n",
            '```bash',
            'git clone https://github.com/rcnchris/sf64-base.git my-project-dir',
            'cd my-project-dir',
            'composer app-install',
            '```',
            "\n### Mise à jour\n",
            '```bash',
            'composer app-update',
            '```',
            "\n### Fonctionalités\n",
            "- Tablettes",
            "- Utilisateurs",
            "   - Inscription",
            "   - Authentification",
            "   - Mot de passe oublié",
            "- Logs",
            "   - Formulaire de recherche",
            "- EasyAdmin",
            "- UX Charts",
            "- Pivottable",
            "- Makefile",
            "\n### Todo\n",
            "- Captcha",
            "- PDF",
        ];
        file_put_contents(sprintf('%s/readme.md', $this->getParameter('kernel.project_dir')), join("\n", $readme));
        $this->addLog(ucfirst($this->trans(__FUNCTION__)), ['action' => 'show']);
        return $this->render('home/readme.html.twig', [
            'title' => __FUNCTION__,
            'readme' => $this->getFileContent('readme.md'),
        ]);
    }
}
