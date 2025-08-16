<?php

namespace App\Tests\Security;

use App\Security\Antispam\Puzzle\{PuzzleChallenge, PuzzleGenerator};
use App\Tests\AppKernelTestCase;
use Symfony\Component\HttpFoundation\Response;

class PuzzleTest extends AppKernelTestCase
{
    private ?PuzzleChallenge $challenge;
    private ?PuzzleGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->challenge = static::getContainer()->get(PuzzleChallenge::class);
        $this->generator = static::getContainer()->get(PuzzleGenerator::class);
    }

    protected function tearDown(): void
    {
        $this->challenge = null;
        $this->generator = null;
        parent::tearDown();
    }

    /**
     * PuzzleChallenge
     */

    public function testInstanceChallenge(): void
    {
        self::assertInstanceOf(PuzzleChallenge::class, $this->challenge);
    }

    public function testChallengeGenerateKeyReturnString(): void
    {
        $key = $this->challenge->generateKey();
        self::assertIsString($key);
    }

    public function testChallengeGetSolution(): void
    {
        $key = $this->challenge->generateKey();
        $solution = $this->challenge->getSolution($key);
        self::assertIsArray($solution);
        self::assertCount(2, $solution);
    }

    public function testChallengeGetSolutionWithInvalidKey(): void
    {
        $this->challenge->generateKey();
        self::assertNull($this->challenge->getSolution('123456'));
    }

    public function testChallengeVerifyWithWrongAnswer(): void
    {
        $key = $this->challenge->generateKey();
        self::assertFalse($this->challenge->verify($key, '0-0'));
    }

    public function testChallengeVerifyWithGoodAnswer(): void
    {
        $key = $this->challenge->generateKey();
        $solution = join('-', $this->challenge->getSolution($key));
        self::assertTrue($this->challenge->verify($key, $solution));
    }

    public function testChallengeVerifyWithGoodKeyAndInvalidAnswer(): void
    {
        $key = $this->challenge->generateKey();
        self::assertFalse($this->challenge->verify($key, '1'));
    }

    public function testChallengeVerifyWithoutKey(): void
    {
        self::assertFalse($this->challenge->verify('123456', ''));
    }

    /**
     * PuzzleGenerator
     */

    public function testInstanceGenerator(): void
    {
        self::assertInstanceOf(PuzzleGenerator::class, $this->generator);
    }

    public function testGeneratorGenerateWithGoodKey(): void
    {
        $key = $this->challenge->generateKey();
        $response = $this->generator->generate($key);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testGeneratorGenerateWithInvalidKey(): void
    {
        $response = $this->generator->generate('123456');
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(404, $response->getStatusCode());
    }
}
