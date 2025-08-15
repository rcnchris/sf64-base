<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Form\{ProfileForm, RegistrationFormType, ResetPasswordForm, ResetPasswordRequestForm};
use App\Repository\{TokenRepository, UserRepository};
use App\Security\EmailVerifier;
use App\Service\MailerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/security', name: 'security.')]
final class SecurityController extends AppAbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $dbLogger,
    ) {
        parent::__construct($dbLogger, $translator);
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        MailerService $mailer,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $userRepository->save($user);
            $this->emailVerifier->sendEmailConfirmation(
                'security.verify',
                $user,
                $mailer->makeMail([
                    'html_template' => 'mails/confirmation_email.html.twig',
                    'to' => $user->getEmail(),
                    'subject' => $this->trans('Please Confirm your Email', [], 'VerifyEmailBundle'),
                ])
            );

            $this->addFlash(
                'success',
                $this->trans('An email has been sent to you to confirm your registration.', [], 'VerifyEmailBundle'),
                true,
                [
                    'action' => __FUNCTION__,
                    'entity' => 'User',
                    'entity_id' => $user->getId(),
                ]
            );
            return $this->redirectToRoute('app.home');
        }

        $this->addLog(ucfirst($this->trans(__FUNCTION__)), ['action' => 'show']);
        return $this->render('security/register.html.twig', [
            'title' => __FUNCTION__,
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'verify', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('security.register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('security.register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user, $userRepository);
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash(
                'danger',
                $this->trans('verify_email_error', [], 'VerifyEmailBundle'),
                true,
                [
                    'action' => 'verify',
                    'entity' => 'User',
                    'entity_id' => $user->getId(),
                ]
            );
            return $this->redirectToRoute('security.register');
        }

        $this->addFlash(
            'success',
            $this->trans('Your email address has been verified.', [], 'VerifyEmailBundle'),
            true,
            [
                'action' => 'verify',
                'entity' => 'User',
                'entity_id' => $user->getId(),
            ]
        );

        return $this->redirectToRoute('app.home');
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $this->addLog(ucfirst($this->trans(__FUNCTION__)), [
            'action' => __FUNCTION__,
            'entity' => 'User',
            'last_username' => $lastUsername,
            'error' => $error->getMessage(),
        ]);
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'title' => __FUNCTION__,
        ]);
    }

    /** @codeCoverageIgnore */
    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getAuthUser();
        $form = $this->createForm(ProfileForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user);
            $this->addFlash('toast-success', $this->trans('toast.edit'));
            return $this->redirectToRoute('security.profile');
        }
        $this->addLog($this->trans(__FUNCTION__), [
            'action' => 'show',
            'entity' => 'User',
            'entity_id' => $user->getId(),
        ]);
        return $this->render('security/profile.html.twig', [
            'title' => __FUNCTION__,
            'form' => $form,
        ]);
    }

    #[Route('/forgot-pass', name: 'forgotten_password', methods: ['GET', 'POST'])]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        MailerService $mailer,
    ): Response {
        $email = $request->query->get('email');
        $form = $this->createForm(ResetPasswordRequestForm::class, compact('email'));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->getByPseudoOrEmail($form->get('email')->getData());
            if ($user) {
                // Génération d'un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $start = $this->getNow();
                $end = $start->modify('+1 hours');
                $user->addToken(
                    (new Token())
                        ->setToken($token)
                        ->setStartAt($start)
                        ->setEndAt($end)
                );
                $userRepository->save($user);

                // lien de réinitialisation du mot de passe
                $url = $this->generateUrl(
                    'security.reset_password',
                    compact('token'),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // mail
                $mailer->makeMail([
                    'to' => $user->getEmail(),
                    'subject' => $this->trans(
                        'Reset password on {{ name }}',
                        ['{{ name }}' => $this->getParameter('app.name')],
                        'security'
                    ),
                    'html_template' => 'mails/reset_password.html.twig',
                    'context' => [
                        'contact' => $user->getPseudo(),
                        'created' => $user->getCreatedAt(),
                        'url' => $url,
                    ],
                ], true);

                $msg = $this->trans(
                    'A password reset email has been sent to {{ email }}.',
                    ['{{ email }}' => $user->getEmail()],
                    'security'
                );
                $this->addFlash('success', $msg);
                return $this->redirectToRoute('security.login');
            }
            $this->addFlash(
                'danger',
                "Un problème est survenu lors de la réinitialisation du mot de passe.",
                true,
                [
                    'action' => __FUNCTION__,
                    'entity' => 'User'
                ]
            );
            return $this->redirectToRoute('security.login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'title' => 'password reset',
            'form' => $form,
        ]);
    }

    #[Route('/forgot-pass/{token}', name: 'reset_password', methods: ['GET', 'POST'])]
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        TokenRepository $tokenRepository,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $token = $tokenRepository->findOneBy(compact('token'));
        if ($token) {
            $form = $this->createForm(ResetPasswordForm::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $userRepository->upgradePassword(
                    $token->getUser(),
                    $hasher->hashPassword($token->getUser(), $form->get('password')->getData())
                );
                $tokenRepository->remove($token);
                $tokenRepository->removeExpired();

                $this->addFlash('success', "Mot de passe changé avec succès");
                return $this->redirectToRoute('security.login');
            }
            return $this->render('security/reset_password.html.twig', [
                'title' => 'password reset',
                'form' => $form,
            ]);
        }
        $msg = 'Jeton invalide';
        $this->addFlash(
            'danger',
            $msg,
            true,
            [
                'action' => __FUNCTION__,
                'entity' => 'User'
            ]
        );
        return $this->redirectToRoute('security.login');
    }
}
