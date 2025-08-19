<?php

namespace App\Tests\Command;

use App\Tests\AppKernelTestCase;

final class EnvInstallCommandTest extends AppKernelTestCase
{
    const CMD_NAME = 'app:env-install';


    private function deleteEnvFile(): void
    {
        $filename = sprintf('%s/.env.local', $this->getParameter('kernel.project_dir'));
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function testExecute(): void
    {
        $this->deleteEnvFile();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            1,
            0,
            'root',
            '',
            'localhost',
            3306,
            'app',
            '8.0.30',
            'utf8mb4',
            'localhost',
            1025,
            'y'

        ]);
        $commandTester->execute(['command' => self::CMD_NAME]);
        $commandTester->assertCommandIsSuccessful();
        $this->deleteEnvFile();
    }

    public function testExecuteWithSqlite(): void
    {
        $this->deleteEnvFile();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            1,
            1,
            'localhost',
            1025,
            'y'

        ]);
        $commandTester->execute(['command' => self::CMD_NAME]);
        $commandTester->assertCommandIsSuccessful();
        $this->deleteEnvFile();
    }

    public function testExecuteWithAbort(): void
    {
        $this->deleteEnvFile();
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            1,
            1,
            'localhost',
            1025,
            'n'
        ]);
        self::assertEquals(1, $commandTester->execute(['command' => self::CMD_NAME]));
    }

    public function testExecuteWithFileExists(): void
    {
        $filename = sprintf('%s/.env.local', $this->getParameter('kernel.project_dir'));
        file_put_contents($filename, __FUNCTION__);
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            1,
            1,
            'localhost',
            1025,
            'y',
            'y'
        ]);
        $commandTester->execute(['command' => self::CMD_NAME]);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithFileExistsAbort(): void
    {
        $commandTester = $this->getCommandTester(self::CMD_NAME);
        $commandTester->setInputs([
            1,
            1,
            'localhost',
            1025,
            'y',
            'n'
        ]);
        self::assertEquals(1, $commandTester->execute(['command' => self::CMD_NAME]));
        $this->deleteEnvFile();
    }
}
