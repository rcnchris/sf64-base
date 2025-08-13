<?php

namespace App\Tests\Command;

use App\Tests\AppKernelTestCase;
use Symfony\Component\Console\Exception\RuntimeException;

final class AddUserCommandTest extends AppKernelTestCase
{
    const CMD_NAME = 'app:add-user';

    public function testExecute(): void
    {
        $faker = $this->getFaker();
        $params = [
            '-n' => true,
            '-p' => substr($faker->userName(), 0, 20),
            '-m' => $faker->email(),
            '-w' => $faker->password(),
            '-f' => $faker->firstName(),
            '-l' => $faker->lastName(),
            '-t' => $faker->phoneNumber(),
        ];
        $this->assertCommandExecuteIsSuccessful(self::CMD_NAME, $params);
    }

    public function testExecuteWithAssistant(): void
    {
        $faker = $this->getFaker();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            substr($faker->userName(), 0, 20),
            $faker->email(),
            '1',
            $faker->password(),
            $faker->firstName(),
            $faker->lastName(),
            $faker->phoneNumber(),
            $faker->hexColor(),
            $faker->sentences(3, true),
        ]);
        $commandTester->execute(['command' => self::CMD_NAME]);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithoutPseudo(): void
    {
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([null]);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithEmptyPseudo(): void
    {
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs(['']);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithPseudoExists(): void
    {
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs(['tst']);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithPseudoTooLong(): void
    {
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs(['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithoutEmail(): void
    {
        $faker = $this->getFaker();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            substr($faker->userName(), 0, 20),
            null
        ]);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithEmptyEmail(): void
    {
        $faker = $this->getFaker();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            substr($faker->userName(), 0, 20),
            ''
        ]);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithEmailExists(): void
    {
        $faker = $this->getFaker();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            substr($faker->userName(), 0, 20),
            'tst@sf64-base.fr',
            '1',
            $faker->password(),
        ]);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }

    public function testExecuteWithWrongEmail(): void
    {
        $faker = $this->getFaker();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            substr($faker->userName(), 0, 20),
            'fake'
        ]);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['command' => self::CMD_NAME]);
    }
}
