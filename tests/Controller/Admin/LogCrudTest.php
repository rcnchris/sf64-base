<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\{DashboardController, LogCrudController};
use App\Entity\Log;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;

final class LogCrudTest extends AbstractCrudTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->loginUser(static::getContainer()->get(UserRepository::class)->getByPseudoOrEmail('admin'));
    }

    protected function getControllerFqcn(): string
    {
        return LogCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(Log::class, LogCrudController::getEntityFqcn());
    }

    public function testIndexPage(): void
    {
        $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseIsSuccessful();
    }

    public function testNewPage(): void
    {
        $this->client->request('GET', $this->generateNewFormUrl());
        static::assertResponseIsSuccessful();
    }
}
