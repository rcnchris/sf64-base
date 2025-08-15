<?php

namespace App\DataFixtures;

use App\Entity\Tablette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this
            ->loadTablettes($manager)
            ->loadUsers($manager);
    }

    private function loadTablettes(ObjectManager $manager): self
    {
        $tablette = (new Tablette())
            ->setName('Catégories des articles')
            ->setDescription('Liste des catégories d\'articles')
            ->setIcon('category')
            ->addChild((new Tablette())->setName('Mémo'))
            ->addChild((new Tablette())->setName('Présentation'));
        $manager->persist($tablette);

        $tablette = (new Tablette())
            ->setName('Catégories des favoris')
            ->setDescription('Liste des catégories de favoris')
            ->setIcon('category')
            ->addChild((new Tablette())->setName('Site officiel'))
            ->addChild((new Tablette())->setName('Documentation'))
            ->addChild((new Tablette())->setName('API'));
        $manager->persist($tablette);

        $manager->flush();
        return $this;
    }

    private function loadUsers(ObjectManager $manager): self
    {
        $faker = Factory::create('fr_FR');
        $admin = new User();
        $admin
            ->setEmail('admin@sf64-base.fr')
            ->setPseudo('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true)
            ->setPassword($this->hasher->hashPassword($admin, 'admin'))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setPhone($faker->phoneNumber())
            ->setDescription($faker->realText())
            ->setColor('#e74c3c');
        $manager->persist($admin);

        $demo = new User();
        $demo
            ->setEmail('demo@sf64-base.fr')
            ->setPseudo('demo')
            ->setRoles(['ROLE_APP'])
            ->setIsVerified(true)
            ->setPassword($this->hasher->hashPassword($demo, 'demo'))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setPhone($faker->phoneNumber())
            ->setDescription($faker->realText())
            ->setColor($faker->hexColor());
        $manager->persist($demo);

        $manager->flush();
        return $this;
    }
}
