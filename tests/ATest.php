<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ATest extends KernelTestCase
{
    public function testEnvIsTest(): void
    {
        self::assertSame('test', self::bootKernel()->getEnvironment());
    }
}
