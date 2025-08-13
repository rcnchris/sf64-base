<?php

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class SecurityControllerTest extends AppWebTestCase
{
    public function testRegister(): void
    {
        $client = $this->makeClient();
        $title = "Inscription";

        $crawler = $this->assertRequestIsSuccessful(
            client: $client,
            uri: '/security/register',
            pageTitle: $title,
            selector: 'h1',
            pageContains: $title,
        );

        $repo = $this->getUserRepository();
        $exist = $repo->getByPseudoOrEmail(__FUNCTION__);
        if ($exist) {
            $repo->remove($exist);
        }
        $count = $repo->count([]);

        // $inputChallenge = $crawler->filter('#captcha_challenge');
        // $key = $inputChallenge->attr('value');

        // $challenge = $this->getService(PuzzleChallenge::class);
        // $solution = join('-', $challenge->getSolution($key));

        $client->submitForm($title, [
            'email' => 'me@example.com',
            'pseudo' => __FUNCTION__,
            'plainPassword' => 'password',
            'agreeTerms' => true,
            // 'captcha[challenge]' => $key,
            // 'captcha[answer]' => $solution,
        ]);

        self::assertResponseRedirects('/home');
        self::assertEquals($count + 1, $repo->count([]));
        self::assertEmailCount(1);

        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage();
        self::assertInstanceOf(TemplatedEmail::class, $email);
        self::assertEmailAddressContains($email, 'to', 'me@example.com');
        self::assertEmailHtmlBodyContains($email, 'Bonjour ! Veuillez confirmer votre adresse mail !');

        $messageBody = $email->getHtmlBody();
        self::assertIsString($messageBody);

        preg_match('#(http://localhost/security/verify/email.+)">#', $messageBody, $verifyLink);
        $verifyLink = str_replace('http://localhost', '', htmlspecialchars_decode($verifyLink[1]));

        $client->request('GET', $verifyLink);
        self::assertResponseRedirects('/home');
        $client->followRedirect();

        // Utilisateur mis à jour
        $user = $this->getUserRepository()->getByPseudoOrEmail(__FUNCTION__);
        self::assertContains('ROLE_APP', $user->getRoles());
        self::assertTrue($user->isVerified());
    }

    public function testVerifyEmailWithWrongQueries(): void
    {
        $client = $this->makeClient();

        $client->request('GET', '/security/verify/email');
        self::assertResponseRedirects('/security/register');

        $client->request('GET', '/security/verify/email?id=9999');
        self::assertResponseRedirects('/security/register');

        $client->request('GET', '/security/verify/email?expires=1746938367&id=3&signature=sgX6kUhT%2BnDIsprk99WLj&token=ImAhMR1tEqLfV3');
        self::assertResponseRedirects('/security/register');
    }
}
