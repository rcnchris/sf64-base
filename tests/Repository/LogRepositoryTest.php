<?php

namespace App\Tests\Repository;

use App\DTO\LogsDTO;
use App\Model\LogSearchModel;
use App\Repository\LogRepository;
use App\Tests\AppKernelTestCase;
use Doctrine\ORM\QueryBuilder;

final class LogRepositoryTest extends AppKernelTestCase
{
    private function getRepo(): LogRepository
    {
        return $this->getRepository(LogRepository::class);
    }

    public function testFindListQbReturnQueryBuilder(): void
    {
        $model = new LogSearchModel();
        $model->setMessage('logs');
        $model->setLevels(['200']);
        $model->setUsers([$this->getUserRepository()->findRand('u')]);

        self::assertInstanceOf(QueryBuilder::class, $this->getRepo()->findListQb($model));
    }

    public function testFindSearchByModelReturnLogsDto(): void
    {
        $model = new LogSearchModel();
        $model->setMessage('logs');

        $result = $this->getRepo()->findListQb($model);
        self::assertInstanceOf(LogsDTO::class, current($result->getQuery()->getResult()));
    }

    public function testCountByHour(): void 
    {
        self::assertIsArray($this->getRepo()->countByHour());
    }
}
