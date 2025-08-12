<?php

namespace App\Tests\Repository\Trait;

use App\Entity\Tablette;
use App\Tests\AppKernelTestCase;
use Doctrine\ORM\QueryBuilder;

final class AppRepositoryTraitTest extends AppKernelTestCase
{
    public function testSetLocale(): void
    {
        self::assertNull($this->getTabletteRepository()->setLocale());
    }

    public function testQueryReturnArray(): void
    {
        $items = $this->getTabletteRepository()->query("SELECT * FROM tablette");
        self::assertIsArray($items);
        self::assertNotEmpty($items);

        $item = current($items);
        self::assertIsArray($item);
        self::assertArrayHasKey('id', $item);
    }

    public function testQueryWithParameterReturnArray(): void
    {
        $items = $this
            ->getTabletteRepository()
            ->query("SELECT * FROM tablette where slug = :slug", ['slug' => 'categories-des-articles']);
        self::assertIsArray($items);
        self::assertNotEmpty($items);
        self::assertCount(1, $items);
    }

    public function testQueryWithParameterAndOneResultReturnArray(): void
    {
        $item = $this
            ->getTabletteRepository()
            ->query("SELECT * FROM tablette where slug = :slug", ['slug' => 'categories-des-articles'], true);
        self::assertIsArray($item);
        self::assertNotEmpty($item);
        self::assertArrayHasKey('id', $item);
    }

    public function testFindRandQbReturnQueryBuilder(): void
    {
        $qb = $this->getTabletteRepository()->findRandQb('t', 't.slug = \'categories-des-articles\'');
        self::assertInstanceOf(QueryBuilder::class, $qb);
        $item = $qb->getQuery()->getOneOrNullResult();
        self::assertInstanceOf(Tablette::class, $item);
    }

    public function testFindRandQb(): void
    {
        $items = $this->getTabletteRepository()->findRand('t', 't.lvl = 0', 2);
        self::assertIsArray($items);
        self::assertCount(2, $items);
        self::assertInstanceOf(Tablette::class, current($items));
    }

    public function testFindRand(): void
    {
        $item = $this->getTabletteRepository()->findRand('t', 't.lvl = 0');
        self::assertInstanceOf(Tablette::class, $item);
    }
}
