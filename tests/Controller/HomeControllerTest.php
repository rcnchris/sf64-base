<?php

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HomeControllerTest extends AppWebTestCase
{
    public function testIndexRedirectToHome(): void
    {
        $this->assertRequestRedirectTo(
            uri: '/',
            uriTo: '/home',
            expectedCode: Response::HTTP_FOUND
        );
    }

    public function testHome(): void
    {
        $this->assertRequestIsSuccessful(
            uri: '/home',
            pageTitle: 'Accueil',
        );
    }

    public function testReadme(): void
    {
        $this->assertRequestIsSuccessful('/readme');
    }

    public function testReadmePdf(): void
    {
        $this->assertRequestIsSuccessful('/readme', ['pdf' => true]);
    }

    public function testChangelog(): void
    {
        $this->assertRequestIsSuccessful('/changelog');
    }
}
