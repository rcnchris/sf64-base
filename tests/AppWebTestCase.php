<?php

namespace App\Tests;

use App\Entity\{Tablette};
use App\Repository\{TabletteRepository};
use Doctrine\ORM\{EntityManagerInterface, EntityRepository};
use Faker\{Factory, Generator};
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppWebTestCase extends WebTestCase
{
    public function makeClient(?string $identifier = null, ?bool $randUserAgent = true): KernelBrowser
    {
        $client = static::createClient();
        if ($randUserAgent) {
            $client->setServerParameter('HTTP_USER_AGENT', $this->getFaker()->userAgent());
        }
        // if (!is_null($identifier)) {
        //     $user = $this->getUserRepository()->getByPseudoOrEmail($identifier);
        //     $client->loginUser($user);
        // }
        return $client;
    }

    protected function getParameter(string $name): mixed
    {
        return static::getContainer()
            ->get(ParameterBagInterface::class)
            ->get($name);
    }

    protected function getFaker(?string $locale = null): Generator
    {
        return Factory::create(is_null($locale) ? $this->getParameter('app.locale_country') : $locale);
    }

    protected function getService(string $classname): mixed
    {
        return static::getContainer()->get($classname);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->getService('doctrine')->getManager();
    }

    protected function getRepository(string $entityClassname): EntityRepository
    {
        return $this->getEntityManager()->getRepository($entityClassname);
    }

    // protected function getUserRepository(): UserRepository
    // {
    //     return $this->getRepository(User::class);
    // }

    protected function getTabletteRepository(): TabletteRepository
    {
        return $this->getRepository(Tablette::class);
    }

    protected function assertArrayHasKeys(array $array, array $keys): void
    {
        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $array);
        }
    }
}
