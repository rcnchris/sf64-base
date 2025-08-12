<?php

namespace App\Repository;

use App\Entity\Tablette;
use App\Repository\Trait\AppRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tablette>
 */
class TabletteRepository extends ServiceEntityRepository
{
    use AppRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tablette::class);
    }

    public function findListQb(): QueryBuilder
    {
        $select = [
            't.id',
            't.name',
            't.slug',
            't.icon',
            't.color',
            't.description',
            'count(0)',
        ];
        return $this
            ->createQueryBuilder('t')
            ->select(sprintf('new App\DTO\TablettesDTO(%s)', join(', ', $select)))
            ->leftJoin('t.children', 'c')
            ->groupBy('t.id')
            ->orderBy('t.name');
    }
}
