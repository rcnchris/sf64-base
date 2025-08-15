<?php

namespace App\Repository;

use App\Entity\Log;
use App\Model\LogSearchModel;
use App\Repository\Trait\AppRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 */
class LogRepository extends ServiceEntityRepository
{
    use AppRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function findListQb(LogSearchModel $search): QueryBuilder
    {
        $select = [
            'l.id',
            'l.createdAt',
            'l.message',
            'l.level',
            'l.channel',
            'u.pseudo',
            'JSON_EXTRACT(l.extra, \'$.route\')',
        ];

        $qb = $this
            ->createQueryBuilder('l')
            ->select(sprintf('new App\DTO\LogsDTO(%s)', join(', ', $select)))
            ->leftJoin('l.user', 'u')
            ->orderBy('l.createdAt', 'DESC');

        if ($search->getMessage()) {
            $qb
                ->andWhere('l.message LIKE :msg')
                ->setParameter('msg', '%' . $search->getMessage() . '%');
        }

        if (!empty($search->getUsers())) {
            $qb
                ->andWhere('u.id in (:userIds)')
                ->setParameter('userIds', array_map(fn ($n)=> $n->getId(), $search->getUsers()));
        }

        if (!empty($search->getLevels())) {
            $qb
                ->andWhere('l.level in (:levels)')
                ->setParameter('levels', $search->getLevels());
        }

        // if (!is_null($search->getDaterange())) {
        //     $dtr = Tools::extractDaterange($search->getDaterange());
        //     $qb
        //         ->andWhere($qb->expr()->between('l.createdAt', ':start', ':end'))
        //         ->setParameter('start', $dtr['start'])
        //         ->setParameter('end', $dtr['end']);
        // }

        return $qb;
    }

    public function countByAllTime(): array
    {
        $this->setLocale();
        $select = [
            'YEAR(l.createdAt) as Annee',
            'DATE_FORMAT(l.createdAt, \'%m-%M\') as Mois',
            'DATE_FORMAT(l.createdAt, \'%d\') as Jour',
            'count(0) as cnt'
        ];
        return $this->createQueryBuilder('l')
            ->select(join(', ', $select))
            ->groupBy('Annee')
            ->addGroupBy('Mois')
            ->addGroupBy('Jour')
            ->orderBy('Annee')
            ->addOrderBy('Mois')
            ->getQuery()
            ->getArrayResult();
    }

    public function countByDateFormatQb(?string $format = '%Y-%m-%d', ?string $alias = 'periode'): QueryBuilder
    {
        $select = [
            sprintf('DATE_FORMAT(l.createdAt, \'%s\') as %s', $format, $alias),
            'count(0) as cnt'
        ];
        return $this->createQueryBuilder('l')
            ->select(join(', ', $select))
            ->groupBy($alias)
            ->orderBy($alias, 'ASC');
    }

    public function countByHour(): array
    {
        return $this->countByDateFormatQb('%H', 'hour')->getQuery()->getArrayResult();
    }

    public function countByDays(): array
    {
        $this->setLocale();
        return $this->countByDateFormatQb('%w-%W', 'day')->getQuery()->getArrayResult();
    }
}
