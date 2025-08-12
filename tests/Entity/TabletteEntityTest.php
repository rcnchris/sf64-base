<?php

namespace App\Tests\Entity;

use App\Entity\Tablette;
use App\Repository\TabletteRepository;
use App\Tests\AppKernelTestCase;

final class TabletteEntityTest extends AppKernelTestCase
{
    private function getRepo(): TabletteRepository
    {
        return $this->getRepository(TabletteRepository::class);
    }

    private function getLastInsert(): Tablette
    {
        return $this->getRepo()->getLastInsert();
    }

    public function testCreate(): void
    {
        $faker = $this->getFaker();

        $data = [
            'name' => $faker->words(3, true),
            'icon' => 'bi:house',
            'color' => $faker->hexColor(),
            'description' => $faker->sentences(3, true),
            'parent' => $this->getRepo()->findRand('t'),
        ];

        $count = $this->getRepo()->count([]);
        $entity = new Tablette();
        $entity
            ->setName($data['name'])
            ->setIcon($data['icon'])
            ->setColor($data['color'])
            ->setDescription($data['description'])
            ->setParent($data['parent']);
        $this->getRepo()->save($entity);
        self::assertEquals($count + 1, $this->getRepo()->count([]));

        $tablette = $this->getLastInsert();
        self::assertSame($data['name'], $tablette->getName());
        self::assertSame($data['icon'], $tablette->getIcon());
        self::assertSame($data['color'], $tablette->getColor());
        self::assertSame($data['description'], $tablette->getDescription());
        self::assertEquals($data['parent'], $tablette->getParent());

        self::assertIsInt($tablette->getLft());
        self::assertIsInt($tablette->getRgt());
        self::assertIsInt($tablette->getLvl());

        // Identifiant
        self::assertIsInt($tablette->getId());

        // Champs calculés
        self::assertIsString($tablette->getSlug());
        self::assertSame($data['name'], (string)$tablette);

        // Traits
        $this->assertEntityUseDatefieldTrait($tablette);

        // Relations
        self::assertInstanceOf(Tablette::class, $tablette->getRoot());
        self::assertEmpty($tablette->getChildren());
    }

    public function testAddAndDeleteChild(): void
    {
        $tablette = $this->getLastInsert();
        self::assertEmpty($tablette->getChildren());

        $child = (new Tablette())
            ->setName(__FUNCTION__)
            ->setIcon('bi:house');

        $tablette->addChild($child);
        $this->getRepo()->save($tablette);
        self::assertCount(1, $tablette->getChildren());
        self::assertInstanceOf(Tablette::class, $child->getParent());


        self::assertInstanceOf(Tablette::class, $tablette->removeChild($child));
        $this->getRepo()->save($tablette);
        self::assertEmpty($tablette->getChildren());
        self::assertNull($child->getParent());
    }
}
