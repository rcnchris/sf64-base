<?php

namespace App\Tests\Service;

use App\Service\MailerService;
use App\Tests\AppKernelTestCase;
use Symfony\Component\Mime\Email;

final class MailerServiceTest extends AppKernelTestCase
{
    private ?MailerService $service = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = static::getContainer()->get(MailerService::class);
    }

    protected function tearDown(): void
    {
        $this->service = null;
        parent::tearDown();
    }

    public function testGetConfigWithoutKeyReturnArray(): void
    {
        $config = $this->service->getMailerConfig();
        self::assertIsArray($config);
    }

    public function testMakeMailWithCustomOptions(): void
    {
        $faker = $this->getFaker();
        $options = [
            'to' => $faker->email(),
            'cc' => $faker->email(),
            'bcc' => $faker->email(),
            'html' => '<p>Ola les gens</p>',
            'priority' => Email::PRIORITY_HIGH,
            'joined' => [__FILE__]
        ];
        $email = $this->service->makeMail($options);
        self::assertIsArray($email->getFrom());
        self::assertIsArray($email->getTo());
        self::assertIsArray($email->getCc());
        self::assertIsArray($email->getBcc());
        self::assertIsString($email->getHtmlBody());
        self::assertIsArray($email->getAttachments());
    }

    public function testMakeMailAndSend(): void
    {
        $faker = $this->getFaker();
        $options = [
            'to' => $faker->email(),
            'subject' => __FUNCTION__,
            'text' => 'Ola les gens',
            'joined' => __FILE__
        ];
        $this->service->makeMail($options, true);
        self::assertEmailCount(1);
    }

    public function testMakeMailWithTextTemplate(): void
    {
        $faker = $this->getFaker();
        $options = [
            'to' => $faker->email(),
            'subject' => __FUNCTION__,
            'txt_template' => 'mails/test.txt.twig',
            'joined' => __FILE__
        ];
        $this->service->makeMail($options, true);
        self::assertEmailCount(1);
    }
}
