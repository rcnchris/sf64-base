<?php

namespace App\Tests;

use App\Repository\{TabletteRepository};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\{Factory, Generator};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppKernelTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;
    protected TabletteRepository $tabRepo;
    // protected UserRepository $userRepo;
    protected Generator $faker;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        // $this->userRepo = static::getContainer()->get(UserRepository::class);
        $this->tabRepo = static::getContainer()->get(TabletteRepository::class);
        $this->faker = Factory::create('fr_FR');
    }

    protected function getParameter(string $name): string
    {
        return static::getContainer()
            ->get(ParameterBagInterface::class)
            ->get($name);
    }

    protected function getRootDir(?string $path): string
    {
        return $this->getParameter('kernel.project_dir') . $path;
    }

    protected function getRepository(string $repoClassname): ServiceEntityRepository
    {
        return static::getContainer()->get($repoClassname);
    }

    protected function getTabletteRepository(): TabletteRepository
    {
        return static::getContainer()->get(TabletteRepository::class);
    }

    /**
     * Générateur de données aléatoires
     */
    protected function getFaker(?string $locale = null): Generator
    {
        return Factory::create(is_null($locale) ? $this->getParameter('app.locale_country') : $locale);
    }

    /** COMMANDS */

    protected function getCommandTester(string $cmdName, ?bool $tester = true): Command|CommandTester
    {
        $application = new Application(self::bootKernel());
        $command = $application->find($cmdName);
        return $tester === true ? new CommandTester($command) : $command;
    }

    /** ASSERTS COMMANDS */

    protected function assertCommandExecuteIsSuccessful(string $cmdName, ?array $params = []): void
    {
        $cmdTester = $this->getCommandTester($cmdName);
        $cmdTester->execute($params);
        $cmdTester->assertCommandIsSuccessful(sprintf('La commande %s retourne une erreur', $cmdName));
    }

    /** ASSERTS ENTITY TRAITS */
    protected function assertEntityUseDatefieldTrait(object $entity): void
    {
        $this->assertTrue(in_array('App\Entity\Trait\DateFieldTrait', class_uses($entity)));
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
    }

    protected function assertEntityUseIntervalFieldTrait(object $entity): void
    {
        $this->assertTrue(in_array('App\Entity\Trait\IntervalFieldTrait', class_uses($entity)));
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getStartAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getEndAt());
        $this->assertInstanceOf(\DateInterval::class, $entity->getIntervalStart());
        $this->assertInstanceOf(\DateInterval::class, $entity->getIntervalEnd());
        $this->assertInstanceOf(\DatePeriod::class, $entity->getPeriode());
        $this->assertTrue($entity->isCurrent());
        $this->assertFalse($entity->isPast());
        $this->assertFalse($entity->isFuture());
    }
}
