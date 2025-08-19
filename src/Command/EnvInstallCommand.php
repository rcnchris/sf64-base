<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\{ChoiceQuestion, ConfirmationQuestion, Question};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:env-install',
    description: 'Création fichier d\'environnement',
)]
class EnvInstallCommand extends Command
{
    private ?QuestionHelper $helper = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $rootDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp($this->getCommandHelp());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->helper = new QuestionHelper();

        $io->title('Assistant de création d\'un fichier d\'environnement');
        $io->section('Environnement');

        // Environnement
        $envs = ['dev', 'local', 'test'];
        $question = new ChoiceQuestion('Environnement (dev) :', $envs, 0);
        $env = $this->helper->ask($input, $output, $question);
        $filename = sprintf('%s/.env.%s', $this->rootDir, $env);

        $io->section('Base de données');

        // SGBD
        $sgbds = ['mysql', 'sqlite', 'postgresql'];
        $question = new ChoiceQuestion('SGBD (mysql) :', $sgbds, 0);
        $question->setErrorMessage('SGBD %s est invalide.');
        $sgbd = $this->helper->ask($input, $output, $question);

        if ($sgbd !== 'sqlite') {
            // Utilisateur bdd
            $question = new Question('Utilisateur base de données (root) : ', 'root');
            $dbUser = $this->helper->ask($input, $output, $question);

            // Utilisateur bdd
            $question = new Question(sprintf('Mot de passe de l\'utilisateur %s (vide) : ', $dbUser), '');
            $dbPwd = $this->helper->ask($input, $output, $question);

            // Host bdd
            $question = new Question('Serveur base de données (localhost) : ', 'localhost');
            $dbHost = $this->helper->ask($input, $output, $question);

            // Port bdd
            $question = new Question('Port base de données (3306) : ', 3306);
            $dbPort = $this->helper->ask($input, $output, $question);

            // Nom bdd
            $question = new Question('Nom base de données (app) : ', 'app');
            $dbName = $this->helper->ask($input, $output, $question);

            // Version host bdd
            $question = new Question('Version serveur base de données (8.0.30) : ', '8.0.30');
            $dbVersion = $this->helper->ask($input, $output, $question);

            // Charset host bdd
            $question = new Question('Charset base de données (utf8mb4) : ', 'utf8mb4');
            $dbCharset = $this->helper->ask($input, $output, $question);
        }

        $io->section('Mailer');

        // Host mailer
        $question = new Question('Serveur SMTP (localhost) : ', 'localhost');
        $mailerHost = $this->helper->ask($input, $output, $question);

        // Port host mailer
        $question = new Question('Port SMTP (1025) : ', 1025);
        $mailerPort = $this->helper->ask($input, $output, $question);

        if ($sgbd === 'sqlite') {
            $io->info([
                sprintf('Environnement : %s', $env),
                sprintf('SGBD : %s', $sgbd),
            ]);
            $databaseUrl = sprintf('DATABASE_URL="%s:///%%kernel.project_dir%%/var/data_%%kernel.environment%%.db"', $sgbd);
        } else {
            $io->info([
                sprintf('Environnement : %s', $env),
                sprintf('SGBD : %s', $sgbd),
                sprintf('Serveur BDD : %s', $dbHost),
                sprintf('Utilisateur : %s', $dbUser),
                sprintf('Base de données : %s', $dbName),
            ]);
            $databaseUrl = sprintf(
                'DATABASE_URL="%s://%s:%s@%s:%d/%s?serverVersion=%s&charset=%s"',
                $sgbd,
                $dbUser,
                $dbPwd,
                $dbHost,
                $dbPort,
                $dbName,
                $dbVersion,
                $dbCharset
            );
        }
        $mailerDsn = sprintf('MAILER_DSN=smtp://%s:%s', $mailerHost, $mailerPort);
        $io->comment([
            $databaseUrl,
            $mailerDsn,
        ]);

        $question = new ConfirmationQuestion("Continuer (y/n) ?\n", false);
        if (!$this->helper->ask($input, $output, $question)) {
            $io->warning('Traitement annulé.');
            return Command::FAILURE;
        }

        if (file_exists($filename)) {
            $question = new ConfirmationQuestion(sprintf(
                "Le fichier \"%s\" existe. Voulez-vous l'écraser (y/n) ?\n",
                basename($filename)
            ), false);
            if (!$this->helper->ask($input, $output, $question)) {
                $io->warning('Traitement annulé.');
                return Command::FAILURE;
            }
        }

        file_put_contents(
            $filename,
            join(PHP_EOL, [$databaseUrl, $mailerDsn]),
            $env === 'test' ? FILE_APPEND : 0
        );

        $io->success(sprintf('Fichier d\'environnement "%s" créé.', basename($filename)));
        return Command::SUCCESS;
    }

    private function getCommandHelp(): string
    {
        return <<<'HELP'
La commande <info>%command.name%</info> permet de crer les fichiers d'environnement du projet.

Utilisation :

    <info>php %command.full_name%</info> --env=dev

HELP;
    }
}
