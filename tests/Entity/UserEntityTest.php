<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AppKernelTestCase;

final class UserEntityTest extends AppKernelTestCase
{
    private function getRepo(): UserRepository
    {
        return $this->getRepository(UserRepository::class);
    }

    private function getLastInsert(): User
    {
        return $this->getRepo()->getLastInsert();
    }

    public function testCreateUser(): void
    {
        $faker = $this->getFaker();
        $data = [
            'email' => $faker->email(),
            'pseudo' => $faker->userName(),
            'password' => $faker->password(),
            'roles' => ['ROLE_APP'],
            'description' => $faker->sentences(3, true),
            'firstname' => $faker->firstName(),
            'lastname' => $faker->lastName(),
            'phone' => $faker->phoneNumber(),
            'color' => $faker->hexColor(),
        ];

        $user = new User();
        $user
            ->setEmail($data['email'])
            ->setPseudo(substr($data['pseudo'], 0, 20))
            ->setPassword($data['password'])
            ->setRoles($data['roles'])
            ->setIsVerified(true)
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setPhone($data['phone'])
            ->setColor($data['color'])
            ->setDescription($data['description']);
        $this->getRepo()->save($user);

        self::assertIsInt($user->getId());
        self::assertSame(substr($data['pseudo'], 0, 20), $user->getPseudo());
        self::assertSame($data['email'], $user->getEmail());
        self::assertContains('ROLE_USER', $user->getRoles());
        self::assertContains('ROLE_APP', $user->getRoles());
        self::assertTrue($user->isVerified());
        self::assertSame($data['password'], $user->getPassword());
        self::assertSame($data['firstname'], $user->getFirstname());
        self::assertSame($data['lastname'], $user->getLastname());
        self::assertSame($data['phone'], $user->getPhone());
        self::assertSame($data['color'], $user->getColor());
        self::assertSame($data['description'], $user->getDescription());

        $this->assertEntityUseDatefieldTrait($user);

        self::assertSame(substr($data['pseudo'], 0, 20), (string)$user);
        self::assertSame(substr($data['pseudo'], 0, 20), $user->getUserIdentifier());
        self::assertSame(sprintf('%s %s', $data['firstname'], $data['lastname']), $user->getFullname());

        // self::assertEmpty($user->getLogs());
    }
}
