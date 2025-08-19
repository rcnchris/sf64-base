<?php

namespace App\Tests\Utils;

use App\Tests\AppTestCase;
use App\Utils\Collection;
use App\Utils\Tools;

final class ToolsTest extends AppTestCase
{
    public function testBytesToHumanSize(): void
    {
        $value = 4096;
        $sizeInOctets = Tools::bytesToHumanSize($value);
        self::assertIsString($sizeInOctets);
        self::assertEquals('4 KB', $sizeInOctets);
    }

    public function testNamesplit(): void
    {
        $parts = Tools::namespaceSplit($this);
        self::assertIsArray($parts);
        self::assertCount(4, $parts);
        self::assertEquals('App', $parts[0]);
        self::assertEquals('Tests', $parts[1]);
        self::assertEquals('Utils', $parts[2]);
        self::assertEquals('ToolsTest', $parts[3]);

        $shortName = Tools::namespaceSplit($this, true);
        self::assertIsString($shortName);
        self::assertEquals('ToolsTest', $shortName);
    }

    public function testGetRandColor(): void
    {
        $color = Tools::getRandColor();
        self::assertEquals(7, strlen($color));

        $color = Tools::getRandColor(true);
        self::assertEquals(7, strlen($color));
    }

    public function testGetRandUser(): void
    {
        $keys = ['username', 'email', 'firstname', 'lastname', 'birthday', 'phone', 'password', 'color'];
        $user = Tools::getRandUser();
        self::assertIsArray($user);
        self::assertCount(count($keys), $user);
        $this->assertArrayHasKeys($user, $keys);
    }

    public function testExtractDaterange(): void
    {
        self::assertIsArray(Tools::extractDaterange());
        self::assertEmpty(Tools::extractDaterange());

        $daterange = '15/10/1975 09:00 - 20/10/1975 17:00';
        $result = Tools::extractDaterange($daterange);
        self::assertIsArray($result);
        $this->assertArrayHasKeys($result, ['start', 'end']);
        self::assertInstanceOf(\DateTimeImmutable::class, $result['start']);
        self::assertInstanceOf(\DateTimeImmutable::class, $result['end']);
    }

    public function testXmlToArrayWithFilename(): void
    {
        $filename = $this->getRootDir('/tests/files/rates.xml');
        $result = Tools::xmlToArray($filename);
        self::assertIsArray($result);
    }

    public function testXmlToArrayWithXmlString(): void
    {
        $filename = $this->getRootDir('/tests/files/rates.xml');
        $result = Tools::xmlToArray(file_get_contents($filename));
        self::assertIsArray($result);
    }

    public function testXmlToArrayWithSimpleXmlElement(): void
    {
        $filename = $this->getRootDir('/tests/files/rates.xml');
        $result = Tools::xmlToArray(simplexml_load_file($filename, 'SimpleXMLElement', LIBXML_NOCDATA));
        self::assertIsArray($result);
    }

    public function testXmlToArrayWithNoXmlFileReturnLogicException(): void
    {
        $filename = $this->getRootDir('/tests/files/city.txt');
        $this->expectException(\LogicException::class);
        Tools::xmlToArray($filename);
    }

    public function testXmlToArrayWithUnknowFileReturnException(): void
    {
        $filename = $this->getRootDir('/tests/files/fake.txt');
        $this->expectException(\Exception::class);
        Tools::xmlToArray($filename);
    }

    public function testXmlToArrayWithNoXmlContentReturnException(): void
    {
        $content = file_get_contents($this->getRootDir('/tests/files/city.txt'));
        $this->expectException(\Exception::class);
        Tools::xmlToArray($content);
    }

    public function testConvertColor(): void
    {
        $rgb = [13, 0, 255];
        $hexa = '#0d00ff';
        self::assertEquals($hexa, Tools::convertColor($rgb));
        self::assertEquals($rgb, Tools::convertColor($hexa));
        self::assertEquals([
            'r' => $rgb[0],
            'g' => $rgb[1],
            'b' => $rgb[2],
        ], Tools::convertColor($hexa, true));
    }

    public function testConvertColorWithoutDashHexaToRgb(): void
    {
        $rgb = [13, 0, 255];
        $hexa = '0d00ff';
        self::assertEquals($rgb, Tools::convertColor($hexa));
    }

    public function testConvertColorWithTooLongHexaToRgbReturnException(): void
    {
        $this->expectException(\Exception::class);
        Tools::convertColor('123456789');
    }

    public function testConvertColorWithRgbToHexaWithWrongLengthArrayReturnException(): void
    {
        $this->expectException(\Exception::class);
        Tools::convertColor([13, 0, 255, 123]);
    }

    public function testConvertTextWithNullReturnEmptyString(): void
    {
        self::assertSame('', Tools::convertText());
    }

    public function testConvertText(): void
    {
        self::assertIsString(Tools::convertText('Oyé les gens'));
    }

    public function testRemoveDirTreeWithNotExistPathReturnFalse(): void
    {
        $path = $this->getRootDir('/tests/files/fakedir');
        self::assertFalse(Tools::removeDirTree($path));
    }

    public function testRemoveDirTree(): void
    {
        $path = $this->getRootDir(sprintf('/tests/files/%s', __FUNCTION__));
        if (!is_dir($path)) {
            mkdir($path);
            $filename = sprintf('%s/%s.txt', $path, __FUNCTION__);
            file_put_contents($filename, 'ola les gens');
        }
        self::assertTrue(Tools::removeDirTree($path));
    }
}
