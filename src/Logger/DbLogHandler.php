<?php

namespace App\Logger;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\{Level, LogRecord};
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

#[AbstractProcessingHandler(Level::Info)]
final class DbLogHandler extends AbstractProcessingHandler
{
    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $tz,
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack
    ) {
        parent::__construct();
    }

    protected function write(LogRecord $record): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!is_null($request)) {
            $log = (new Log())
                ->setMessage($record['message'])
                ->setLevel($record['level'])
                ->setLevelName($record['level_name'])
                ->setChannel('db')
                ->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone($this->tz)));

            $extra = $record->extra;
            if (array_key_exists('user', $extra)) {
                $log->setUser($extra['user']);
                unset($extra['user']);
            }

            $this->em->persist($log->setContext($record->context)->setExtra($extra));
            $this->em->flush();
        }
    }
}
