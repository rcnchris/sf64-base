<?php

namespace App\Controller;

use App\Service\PdfService;
use Symfony\Component\HttpFoundation\{Request, Response};
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
    public function readme(Request $request, PdfService $pdfService): Response
    {
        if ($request->query->has('pdf')) {
            $filename = sprintf('%s/readme.pdf', $this->getParameter('app.docs_dir'));
            $pdf = $pdfService->make([
                'title' => $this->getParameter('app.name'),
            ]);
            $pdf
                ->setFontStyle(style: 'B', size: 14)
                ->print('Installation')
                ->printCode([
                    'git clone https://github.com/rcnchris/sf64-base.git my-project-dir',
                    'cd my-project-dir',
                    'composer app-install'
                ])
                ->addLn(5)
                ->setFontStyle(style: 'B', size: 14)
                ->print('Mise à jour')
                ->printCode('composer app-update')
                ->addLn(5)
                ->setFontStyle(style: 'B', size: 14)
                ->print('Fonctionnalités')
                ->setFontStyle(style: '', size: 10)
                ->printBulletArray([
                    'Tablettes' => [
                        'GedmoTree'
                    ],
                    'Utilisateurs' => [
                        'Inscription (captcha)',
                        'Authentification',
                        'Mot de passe oublié',
                    ],
                    'Logs' => [
                        'Formulaire de recherche'
                    ],
                    'EasyAdmin',
                    'Formulaires' => [
                        'CK Editor',
                        'DateRangePicker',
                        'Input Mask',
                        'DualListBox',
                    ],
                    'Datatables',
                    'FullCalendar',
                    'UX Charts',
                    'Pivottable',
                    'Makefile',
                    'PDF',
                ])
                ->render('F', $filename);
            return $this->file($filename);
        }

        $installCmds = [
            'git clone https://github.com/rcnchris/sf64-base.git my-project-dir',
            'cd my-project-dir',
            'composer app-install',
            "php bin/console app:env-install",
            'git commit -m "Création projet"',
            "code .",
        ];

        $composer = json_decode($this->getFileContent('composer.json'), true);
        $readme = [
            sprintf("## %s\n", $composer['description']),
            sprintf("Version : **%s**", $composer['version']),
            "\n### Fonctionalités\n",
            "- Tablettes",
            "- Utilisateurs",
            "   - Inscription (captcha)",
            "   - Authentification",
            "   - Mot de passe oublié",
            "- Logs",
            "   - Formulaire de recherche",
            "- EasyAdmin",
            "- Formulaires",
            "   - CK Editor",
            "   - DateRangePicker",
            "   - Input Mask",
            "   - DualListBox",
            "- Datatables",
            "- FullCalendar",
            "- UX Charts",
            "- Pivottable",
            "- Makefile",
            "- PDF",
            "   - Signets",
            "   - Fichiers attachés",
            "   - Rotation texte et image",
            "   - Géométrie",
            "   - Etiquettes",
            "   - AutoPrint",
            "   - Graphique camembert et histogramme",
            "   - Codes à barres",
            "### Installation",
            sprintf("```bash\n%s\n```", join(PHP_EOL, $installCmds))
        ];
        file_put_contents(sprintf('%s/readme.md', $this->getParameter('kernel.project_dir')), join("\n", $readme));
        $this->addLog(ucfirst($this->trans(__FUNCTION__)), ['action' => 'show']);
        return $this->render('home/readme.html.twig', [
            'title' => __FUNCTION__,
            'readme' => $this->getFileContent('readme.md'),
        ]);
    }

    #[Route('/changelog', name: 'changelog')]
    public function changelog(): Response
    {
        $filename = sprintf('%s/changelog.md', $this->getParameter('kernel.project_dir'));
        $changelog = file_get_contents($filename);
        $this->addLog(ucfirst($this->trans(__FUNCTION__)), ['action' => 'show']);
        return $this->render('home/changelog.html.twig', [
            'title' => __FUNCTION__,
            'changelog' => $changelog,
        ]);
    }
}
