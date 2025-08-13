<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Tests\AppKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRepositoryTest extends AppKernelTestCase
{
    public function testGetByPseudoOrEmail(): void
    {
        $repo = $this->getUserRepository();

        $user = $repo->getByPseudoOrEmail('tst');
        self::assertInstanceOf(User::class, $user);
        self::assertSame('tst', $user->getPseudo());

        $user = $repo->getByPseudoOrEmail('tst@sf64-base.fr');
        self::assertInstanceOf(User::class, $user);
        self::assertSame('tst', $user->getPseudo());
    }

    public function testGetForAuthentication(): void
    {
        self::assertInstanceOf(User::class, $this->getUserRepository()->getForAuthentication('tst'));
    }

    public function testUpgradePassword(): void
    {
        $repo = $this->getUserRepository();
        $user = $repo->getByPseudoOrEmail('tst');
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertNull($repo->upgradePassword($user, $hasher->hashPassword($user, 'tsttst')));
    }
}
