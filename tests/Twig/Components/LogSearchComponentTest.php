<?php

namespace App\Tests\Twig\Components;

use App\Tests\AppKernelTestCase;
use App\Twig\Components\LogSearch;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

/**
 * @link https://symfony.com/bundles/ux-live-component/current/index.html#test-helper
 */
class LogSearchComponentTest extends AppKernelTestCase
{
    use InteractsWithLiveComponents;

    public function testCanRenderAndInteract(): void
    {
        $component = $this->createLiveComponent(
            name: LogSearch::class,
            data: ['query' => 'demo'],
        );

        // render the component html
        $this->assertStringContainsString('Recherche', $component->render());

        // customize the test client
        $client = self::getContainer()->get('test.client');

        // do some stuff with the client (ie login user via form)

        $component = $this->createLiveComponent(
            name: LogSearch::class,
            data: ['query' => 'demo'],
            client: $client,
        );
    }
}
