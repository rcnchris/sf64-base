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

    /**
     * Traduit un contenu et le retourne
     * 
     * @param string $content Contenu à traduire
     * @param array $params Paramètres de traduction
     * @param ?string $domain Nom du domaine de traduction
     * @param ?string $locale Langue de la traduction
     */
    protected function trans(
        string $content,
        array $params = [],
        ?string $domain = null,
        ?string $locale = null
    ): string {
        return $this->translator->trans($content, $params, $domain, $locale);
    }
}
