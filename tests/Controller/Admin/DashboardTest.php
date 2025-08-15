<?php

namespace App\Tests\Controller\Admin;

use App\Tests\AppWebTestCase;

final class DashboardTest extends AppWebTestCase
{
    public function testAdminWhenNotAuthenticatedRedirectToLogin(): void
    {
        $client = $this->makeClient();
        $client->request('GET', '/admin');
        self::assertResponseRedirects('/security/login');
        $client->followRedirect();
    }

    public function testAdminWithNotAdminReturn403(): void
    {
        $client = $this->makeClient('tst');
        $client->request('GET', '/admin');
        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminWithAdminSuccessful(): void
    {
        $client = $this->makeClient('admin');
        $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();
    }
}
