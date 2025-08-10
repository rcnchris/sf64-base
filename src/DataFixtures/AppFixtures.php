<?php

namespace App\DataFixtures;

use App\Entity\Tablette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadTablettes($manager);
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
}
