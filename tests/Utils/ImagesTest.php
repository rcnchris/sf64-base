<?php

namespace App\Tests\Utils;

use App\Tests\AppTestCase;
use App\Utils\Images;
use Intervention\Image\Image;

final class ImagesTest extends AppTestCase
{
    public function testGetImageWithoutParameter(): void
    {
        self::assertInstanceOf(Image::class, Images::make());
    }

    public function testGetImageWithPath(): void
    {
        $filename = $this->getRootDir('/assets/images/logo.png');
        self::assertInstanceOf(Image::class, Images::make($filename));
    }

    public function testGetImageWithImageInstance(): void
    {
        $filename = $this->getRootDir('/assets/images/logo.png');
        $image = Images::make($filename);
        self::assertInstanceOf(Image::class, Images::make($image));
    }

    public function testEncode(): void
    {
        $filename = $this->getRootDir('/assets/images/logo.png');
        $encoded = Images::encode($filename);
        self::assertIsString($encoded);
        self::assertStringStartsWith('data:image/png;base64,', $encoded);
    }
}
