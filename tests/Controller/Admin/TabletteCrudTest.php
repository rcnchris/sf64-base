<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\{DashboardController, TabletteCrudController};
use App\Entity\Tablette;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;

final class TabletteCrudTest extends AbstractCrudTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->loginUser(static::getContainer()->get(UserRepository::class)->getByPseudoOrEmail('admin'));
    }

    protected function getControllerFqcn(): string
    {
        return TabletteCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(Tablette::class, TabletteCrudController::getEntityFqcn());
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
