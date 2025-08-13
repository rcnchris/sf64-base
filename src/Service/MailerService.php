<?php

namespace App\Service;

use App\Controller\AppAbstractController;
use App\Repository\TabletteRepository;
use App\Utils\Images;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\{Address, Email};
use Symfony\Component\Yaml\Yaml;

final class MailerService extends AppAbstractController
{
    private array $mailerConfig = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootPath,
        #[Autowire('%app.logo%')]
        private readonly string $logoPath,
        #[Autowire('%app.name%')]
        private readonly string $appName,
        private readonly MailerInterface $mailer,
        private readonly TabletteRepository $tabletteRepository,
        private readonly LoggerInterface $dbLogger,
        private readonly RequestStack $requestStack,
    ) {
        $this->mailerConfig = Yaml::parseFile(sprintf(
            '%s/config/packages/mailer.yaml',
            $rootPath
        ));
    }

    /**
     * Crée un mail et le retourne
     * 
     * @param ?array $options Options du mail dans un tableau associatif
     * @param ?bool $send Si vrai, le mail est envoyé
     */
    public function makeMail(?array $options = [], ?bool $send = false): Email|TemplatedEmail
    {
        // Email or TemplatedEmail
        if (
            array_key_exists('html_template', $options) ||
            array_key_exists('txt_template', $options)
        ) {
            $email = new TemplatedEmail();
            if (array_key_exists('html_template', $options)) {
                $email->htmlTemplate($options['html_template']);
            }
            if (array_key_exists('txt_template', $options)) {
                $email->textTemplate($options['txt_template']);
            }
            $context = array_merge([
                'logo' => Images::encode($this->logoPath),
            ], $options['context'] ?? []);
            $email->context($context);
        } else {
            $email = new Email();
        }

        // From
        if (!array_key_exists('from', $options)) {
            $options['from'] = new Address(
                $this->getMailerConfig('envelope')['sender'],
                $this->getMailerConfig('headers')['X-Custom-Header']
            );
        }
        $email->from($options['from']);

        // To
        if (array_key_exists('to', $options)) {
            $email->to($options['to']);
        }

        // Cc
        if (array_key_exists('cc', $options)) {
            $email->cc($options['cc']);
        }

        // Bcc
        if (array_key_exists('bcc', $options)) {
            $email->bcc($options['bcc']);
        }

        // Subject
        if (array_key_exists('subject', $options)) {
            $email->subject($options['subject']);
        }

        // HTML Body
        if (array_key_exists('html', $options)) {
            $email->html($options['html']);
            $email->text(strip_tags($options['html']));
        }

        // Text Body
        if (array_key_exists('text', $options)) {
            $email->text($options['text']);
        }

        // Joined
        if (array_key_exists('joined', $options)) {
            $joined = $options['joined'];
            if (is_string($joined)) {
                $email->attachFromPath($options['joined']);
            } elseif (is_array($joined)) {
                foreach ($joined as $filename) {
                    $email->attachFromPath($filename);
                }
            }
        }

        // Priority
        array_key_exists('priority', $options)
            ? $email->priority($options['priority'])
            : $email->priority(Email::PRIORITY_NORMAL);

        if ($send) {
            $this->send($email);
        }

        return $email;
    }

    /**
     * Retourne la configuration de mailer ou la valeur d'une clé
     * 
     * @param ?string $key Clé de la configuration à retourner
     */
    public function getMailerConfig(?string $key = null): array|string|null
    {
        $config = $this->mailerConfig['framework']['mailer'];
        return is_null($key)
            ? $config
            : (array_key_exists($key, $config) ? $config[$key] : null);
    }

    /**
     * Envoie le mail spécifié
     * 
     * @param Email|TemplatedEmail $email L'instance du mail à envoyer
     */
    public function send(Email|TemplatedEmail $email): void
    {
        try {
            $this->mailer->send($email);
            $message = sprintf(
                'Le mail %s a été envoyé à %s',
                $email->getSubject(),
                current($email->getTo())->getAddress()
            );
            $this->dbLogger->info($message);
            if ($this->hasSession()) {
                $this->addFlash('toast-success', $message);
            }
        } 
        // @codeCoverageIgnoreStart
        catch (TransportExceptionInterface $e) {
            $message = sprintf(
                'Erreur lors de la tentative d\'envoi du mail "%s" à "%s" avec le message : %s',
                $email->getSubject(),
                current($email->getTo())->getAddress(),
                $e->getMessage()
            );
            $this->dbLogger->error($message);
            if ($this->hasSession()) {
                $this->addFlash('toast-warning', $message);
            }
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Vérifie si une session est active
     */
    private function hasSession(): bool
    {
        return !is_null($this->requestStack->getCurrentRequest());
    }
}
