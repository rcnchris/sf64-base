<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAbstractController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    /**
     * Retourne le contenu d'un fichier du projet
     * 
     * @param string $filepath Chemin relatif du fichier
     */
    protected function getFileContent(string $filepath): string|false
    {
        if (substr($filepath, 0, 1) !== '/') {
            $filepath = '/' . $filepath;
        }
        return file_get_contents(sprintf('%s%s', $this->getParameter('kernel.project_dir'), $filepath));
    }
}
