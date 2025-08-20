<?php

namespace App\Tests;

use App\Entity\{Tablette, User};
use App\Repository\{TabletteRepository, UserRepository};
use Doctrine\ORM\{EntityManagerInterface, EntityRepository};
use Faker\{Factory, Generator};
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;

class AppWebTestCase extends WebTestCase
{
    public function makeClient(?string $identifier = null, ?bool $randUserAgent = true): KernelBrowser
    {
        $client = static::createClient();
        if ($randUserAgent) {
            $client->setServerParameter('HTTP_USER_AGENT', $this->getFaker()->userAgent());
        }
        if (!is_null($identifier)) {
            $user = $this->getUserRepository()->getByPseudoOrEmail($identifier);
            $client->loginUser($user);
        }
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

    protected function getUserRepository(): UserRepository
    {
        return $this->getRepository(User::class);
    }

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

    protected function assertRequestRedirectTo(
        string $uri,
        ?array $params = [],
        ?string $uriTo = null,
        ?string $user = null,
        ?string $method = 'GET',
        ?int $expectedCode = null,
        ?KernelBrowser $client = null,
    ): Crawler {
        if (is_null($client)) {
            $client = $this->makeClient($user);
        }
        $crawler = $client->request($method, $uri, $params);
        self::assertResponseRedirects(
            expectedLocation: $uriTo,
            expectedCode: $expectedCode,
            message: "La requête n'est pas redirigée comme attendu"
        );
        return $crawler;
    }

    protected function assertRequestIsSuccessful(
        string $uri,
        ?array $params = [],
        ?string $user = null,
        ?string $method = 'GET',
        ?string $pageTitle = null,
        ?string $selector = null,
        ?string $pageContains = null,
        ?KernelBrowser $client = null,
    ): Crawler {
        if (is_null($client)) {
            $client = $this->makeClient($user);
        }
        $crawler = $client->request($method, $uri, $params);
        self::assertResponseIsSuccessful();
        if (!is_null($pageTitle)) {
            self::assertPageTitleContains($pageTitle, "Le titre de l'onglet est incorrect");
        }
        if (!is_null($pageContains)) {
            self::assertSelectorTextContains(
                $selector,
                $pageContains,
                sprintf("La page ne contient pas \"%s\" pour le sélecteur \"%s\"", $pageContains, $selector)
            );
        }
        return $crawler;
    }
}
