<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\{ChoiceQuestion, Question};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-user',
    description: 'Ajouter un utilisateur',
)]
class AddUserCommand extends Command
{
    private bool $isInteractive = true;
    private array $data = [];
    private ?QuestionHelper $helper = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            ->addOption('pseudo', 'p', InputOption::VALUE_REQUIRED, 'Pseudo')
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED, 'Courriel')
            ->addOption('roles', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Rôles', [User::ROLES['Application']])
            ->addOption('password', 'w', InputOption::VALUE_REQUIRED, 'Mot de passe')
            ->addOption('firstname', 'f', InputOption::VALUE_OPTIONAL, 'Prénom')
            ->addOption('lastname', 'l', InputOption::VALUE_OPTIONAL, 'Nom')
            ->addOption('phone', 't', InputOption::VALUE_OPTIONAL, 'Téléphone')
            ->addOption('color', 'c', InputOption::VALUE_OPTIONAL, 'Couleur')
            ->addOption('description', 'd', InputOption::VALUE_OPTIONAL, 'Description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->isInteractive = !$input->getOption('no-interaction');

        if ($this->isInteractive) {
            $io->title('Assistant de création d\'un utilisateur');
            $this->helper = $this->getHelper('question');
            $repo = $this->userRepository;

            // Pseudo
            $question = new Question('Pseudo : ', $input->getOption('pseudo'));
            $question->setValidator(function (string|null $answer) use ($repo): string {
                if (is_null($answer)) {
                    throw new RuntimeException('Le pseudo est obligatoire', 1);
                } elseif (strlen($answer) > 20) {
                    throw new RuntimeException(sprintf('Le pseudo %s ne doit pas dépasser 20 caractères', $answer), 1);
                } elseif ($repo->getByPseudoOrEmail($answer)) {
                    throw new RuntimeException(sprintf('Le pseudo %s existe déjà', $answer), 1);
                }
                return $answer;
            });
            $this->data['pseudo'] = $this->helper->ask($input, $output, $question);

            // Email
            $question = new Question('Courriel : ', $input->getOption('email'));
            $question->setValidator(function (string|null $answer) use ($repo): string {
                if (is_null($answer)) {
                    throw new RuntimeException('Le courriel est obligatoire', 1);
                } elseif ($answer !== filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException(sprintf('Le courriel %s est invalide', $answer), 1);
                } elseif ($repo->getByPseudoOrEmail($answer)) {
                    throw new RuntimeException(sprintf('Le courriel %s existe déjà', $answer), 1);
                }
                return $answer;
            });
            $this->data['email'] = $this->helper->ask($input, $output, $question);

            // Rôles
            $question = new ChoiceQuestion('Rôles (ROLE_BO):', array_values(User::ROLES), 1);
            $question
                ->setMultiselect(true)
                ->setErrorMessage('Rôle "%s" invalide.');
            $this->data['roles'] = $this->helper->ask($input, $output, $question);
            $io->note(sprintf('Rôles : %s', join(', ', $this->data['roles'])));

            // Mot de passe
            $this->data['password'] = $this->helper->ask($input, $output, new Question('Mot de passe : '));

            // Prénom
            $this->data['firstname'] = $this->helper->ask($input, $output, new Question('Prénom : '));

            // Nom
            $this->data['lastname'] = $this->helper->ask($input, $output, new Question('Nom : '));

            // Téléphone
            $this->data['phone'] = $this->helper->ask($input, $output, new Question('Téléphone : '));

            // Couleur
            $this->data['color'] = $this->helper->ask($input, $output, new Question('Couleur : '));

            // Description
            $this->data['description'] = $this->helper->ask($input, $output, new Question('Description : '));
        } else {
            $this->data = [
                'pseudo' => $input->getOption('pseudo'),
                'email' => $input->getOption('email'),
                'roles' => $input->getOption('roles'),
                'password' => $input->getOption('password'),
                'firstname' => $input->getOption('firstname'),
                'lastname' => $input->getOption('lastname'),
                'phone' => $input->getOption('phone'),
                'color' => $input->getOption('color'),
                'description' => $input->getOption('description'),
            ];
        }
        $user = $this->createUser();
        $io->success(sprintf('Utilisateur %s créé avec succès', $user->getPseudo()));

        return Command::SUCCESS;
    }

    private function createUser(): User
    {
        $this->data = array_filter($this->data);
        $user = new User();
        $user
            ->setPseudo($this->data['pseudo'])
            ->setEmail($this->data['email'])
            ->setRoles($this->data['roles'])
            ->setPassword($this->hasher->hashPassword($user, $this->data['password']))
            ->setIsVerified(true)
            ->setFirstname($this->data['firstname'] ?? null)
            ->setLastname($this->data['lastname'] ?? null)
            ->setPhone($this->data['phone'] ?? null)
            // ->setColor($this->data['color'] ?? Tools::getRandColor())
            ->setDescription($this->data['description'] ?? null)
        ;
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    private function getCommandHelp(): string
    {
        return <<<'HELP'
La commande <info>%command.name%</info> permet d'ajouter un utilisateur à l'application.

Utilisation :

    <info>php %command.full_name%</info> <comment>jobi</comment> <comment>jobi@demo.fr</comment>

HELP;
    }
}
