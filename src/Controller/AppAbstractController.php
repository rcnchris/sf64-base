<?php

namespace App\Controller;

use App\Entity\{Log, User};
use App\Pdf\AppPdf;
use Faker\{Factory, Generator};
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAbstractController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $dbLogger,
        private readonly TranslatorInterface $translator,
        private readonly PaginatorInterface $paginator,
    ) {}

    /**
     * Retourne l'instance de l'utilisateur authentifié
     */
    protected function getAuthUser(): ?User
    {
        return $this->getUser();
    }

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

    /**
     * Retourne la date et l'heure actuelle
     * 
     * @param ?string $stringTimezone Fuseau horaire au format chaîne de caractères
     */
    protected function getNow(?string $stringTimezone = null): \DateTimeImmutable
    {
        $tz = is_null($stringTimezone) ? $this->getParameter('app.timezone') : $stringTimezone;
        return new \DateTimeImmutable('now', new \DateTimeZone($tz));
    }

    /**
     * Retourne une liste paginée
     * 
     * @param mixed $data Données à paginer
     * @param Request $request Requête
     * @param ?int $limit Nombre d'items par page
     * @param ?array $options Options de la pagination
     */
    protected function paginate(
        mixed $data,
        Request $request,
        ?int $limit = null,
        ?array $options = []
    ): PaginationInterface {
        return $this->paginator->paginate(
            $data,
            $request->query->get('page', 1),
            $limit,
            $options
        );
    }

    /**
     * Ajoute un log en base de données
     * 
     * @param string $message Message du log
     * @param ?array $context Contexte
     * @param ?string $type Type de log
     */
    protected function addLog(string $message, ?array $context = [], ?string $type = 'info'): void
    {
        if (!in_array(strtoupper($type), Log::LEVELS)) {
            switch ($type) {
                case 'danger':
                    $type = 'error';
                    break;

                default:
                    $type = 'info';
                    break;
            }
        }
        $this->dbLogger->{$type}($message, $context);
    }

    /**
     * Ajoute un message flash à la session en cours pour le type.
     * 
     * @param string $type Type du message
     * @param mixed $message Contenu du message
     * @param ?bool Ajoute le message aux logs
     * @param ?array $context Contexte du log
     *
     * @throws \LogicException
     */
    protected function addFlash(string $type, mixed $message, ?bool $addLog = false, ?array $context = []): void
    {
        parent::addFlash($type, $message);

        if ($addLog) {
            $this->addLog($message, $context, $type);
        }
    }

    /**
     * Retourne une instance du générateur de données aléatoires
     * 
     * @param ?string $locale Locale à utiliser
     */
    protected function getFaker(?string $locale = null): Generator
    {
        return Factory::create(is_null($locale) ? $this->getParameter('app.locale_country') : $locale);
    }
}
