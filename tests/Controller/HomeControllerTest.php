<?php

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HomeControllerTest extends AppWebTestCase
{
    public function testIndexRedirectToHome(): void
    {
        $this->makeClient()->request('GET', '/');
        self::assertResponseRedirects('/home', Response::HTTP_FOUND);
    }

    public function testHome(): void
    {
        $this->makeClient()->request('GET', '/home');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Accueil');
        self::assertSelectorTextContains('h1', $this->getParameter('app.name'));
    }

    public function testReadme(): void
    {
        $this->makeClient()->request('GET', '/readme');
        self::assertResponseIsSuccessful();
    }
}
