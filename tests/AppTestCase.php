<?php

namespace App\Tests;

use Faker\{Factory, Generator};
use PHPUnit\Framework\TestCase;

class AppTestCase extends TestCase
{
    protected function getRootDir(?string $path = null): string
    {
        return dirname(__DIR__) . $path;
    }

    protected function getFaker(?string $locale = 'fr_FR'): Generator
    {
        return Factory::create($locale);
    }

    protected function assertArrayHasKeys(array $array, array $keys): void
    {
        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $array);
        }
    }
}
