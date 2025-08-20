<?php

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class TabletteControllerTest extends AppWebTestCase
{
    public function testList(): void
    {
        $this->assertRequestIsSuccessful('/tablette/list');
    }

    public function testNew(): void
    {
        $client = $this->makeClient();
        $faker = $this->getFaker();
        $count = $this->getTabletteRepository()->count([]);
        $client->request('GET', '/tablette/new');
        self::assertResponseIsSuccessful();
        $client->submitForm('Créer', [
            'name' => $faker->words(3, true),
            'icon' => 'bi:house',
            'color' => $faker->hexColor(),
            'description' => $faker->realText(),
            'parent' => $this->getTabletteRepository()->findRand('t')->getId(),
        ]);
        self::assertResponseRedirects('/tablette/list', Response::HTTP_SEE_OTHER);
        self::assertEquals($count + 1, $this->getTabletteRepository()->count([]));
    }

    public function testEdit(): void
    {
        $client = $this->makeClient();
        $faker = $this->getFaker();
        $entity = $this->getTabletteRepository()->findRand('t');
        $uri = sprintf('/tablette/edit/%d/%s', $entity->getId(), $entity->getSlug());
        $client->request('GET', $uri);
        self::assertResponseIsSuccessful();
        $client->submitForm('Enregistrer', [
            'color' => $faker->hexColor(),
        ]);
        self::assertResponseRedirects($uri, Response::HTTP_SEE_OTHER);
    }

    public function testEditWithInvalidSlugReditect(): void
    {
        $client = $this->makeClient();
        $entity = $this->getTabletteRepository()->findRand('t');
        $uri = sprintf('/tablette/edit/%d/fake', $entity->getId());
        $client->request('GET', $uri);
        self::assertResponseRedirects(sprintf('/tablette/edit/%d/%s', $entity->getId(), $entity->getSlug()), Response::HTTP_SEE_OTHER);
    }

    public function testDelete(): void
    {
        $client = $this->makeClient('tst');
        $count = $this->getTabletteRepository()->count([]);
        $entity = $this->getTabletteRepository()->getLastInsert();
        $client->request('GET', sprintf('/tablette/edit/%d/%s', $entity->getId(), $entity->getSlug()));
        $client->submitForm('Supprimer');
        self::assertResponseRedirects('/tablette/list');
        self::assertEquals($count - 1, $this->getTabletteRepository()->count([]));
    }
}
