<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileForm;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/security', name: 'security.')]
final class SecurityController extends AppAbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly TranslatorInterface $translator
    ) {
        parent::__construct($translator);
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
                $this->trans('An email has been sent to you to confirm your registration.', [], 'VerifyEmailBundle')
            );
            return $this->redirectToRoute('app.home');
        }

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
            $this->addFlash('danger', $this->trans('verify_email_error', [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('security.register');
        }

        $this->addFlash('success', $this->trans('Your email address has been verified.', [], 'VerifyEmailBundle'));

        return $this->redirectToRoute('app.home');
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
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
        // $this->addLog($this->trans(__FUNCTION__));
        return $this->render('security/profile.html.twig', [
            'title' => __FUNCTION__,
            'form' => $form,
        ]);
    }
}
