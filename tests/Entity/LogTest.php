<?php

namespace App\Tests\Entity;

use App\Entity\{Log, User};
use App\Repository\LogRepository;
use App\Tests\AppKernelTestCase;

final class LogTest extends AppKernelTestCase
{
    public function testCreateLog(): void
    {
        // $em = $this->getEntityManager();
        $repo = $this->getRepository(LogRepository::class);
        $faker = $this->getFaker();
        $data = [
            'message' => $faker->sentences(3, true),
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'db',
        ];
        $count = $repo->count([]);
        $log = (new Log())
            ->setMessage($data['message'])
            ->setLevel($data['level'])
            ->setLevelName($data['level_name'])
            ->setChannel($data['channel']);
        $repo->save($log);
        self::assertEquals($count + 1, $repo->count([]));

        self::assertIsInt($log->getId());
        self::assertSame($data['message'], $log->getMessage());
        self::assertSame(sprintf('#%d', $log->getId()), (string)$log);
        self::assertEquals($data['level'], $log->getLevel());
        self::assertSame($data['level_name'], $log->getLevelName());
        self::assertSame($data['channel'], $log->getChannel());
        self::assertEmpty($log->getContext());
        self::assertEmpty($log->getExtra());
        self::assertInstanceOf(\DateTimeImmutable::class, $log->getCreatedAt());
        self::assertNull($log->getUser());

        // Add user, context and extra
        $log
            ->setUser($this->getUserRepository()->getByPseudoOrEmail('tst'))
            ->setContext(['file' => __FILE__])
            ->setExtra(['class' => __CLASS__]);
        $repo->save($log);

        self::assertInstanceOf(User::class, $log->getUser());

        self::assertIsArray($log->getContext());
        self::assertArrayHasKey('file', $log->getContext());
        self::assertSame(__FILE__, $log->getContext()['file']);

        self::assertIsArray($log->getExtra());
        self::assertSame(__CLASS__, $log->getExtra()['class']);
        self::assertArrayHasKey('class', $log->getExtra());

        // Suppression entité
        $count = $repo->count([]);
        $repo->remove($log);
        self::assertEquals($count - 1, $repo->count([]));
    }

    // public function testFindSearchByModelReturnQuery(): void
    // {
    //     $model = new LogSearchModel();
    //     $model->setMessage(__FUNCTION__);
    //     $model->setUsers([$this->getUserRepository()->findOneBy([])]);
    //     $model->setDaterange('13/08/2024 00:00 - 13/08/2024 23:59');
    //     $model->setLevels([200]);

    //     $result = $this->getRepository(LogRepository::class)->findListQb($model);
    //     self::assertInstanceOf(QueryBuilder::class, $result);
    // }

    // public function testFindSearchByModelReturnLogsDto(): void
    // {
    //     $model = new LogSearchModel();
    //     $model->setMessage('changements');

    //     $result = $this->getRepository(LogRepository::class)->findListQb($model);
    //     self::assertInstanceOf(LogsDTO::class, current($result->getQuery()->getResult()));
    // }
}
