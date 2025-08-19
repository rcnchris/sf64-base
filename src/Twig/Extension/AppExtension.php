<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\{TwigFilter, TwigFunction};

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('canvas', [AppExtensionRuntime::class, 'canvas'], ['is_safe' => ['html']]),
            new TwigFilter('highlight', [AppExtensionRuntime::class, 'highlight'], ['is_safe' => ['html']]),
            new TwigFilter('format_bytes', [AppExtensionRuntime::class, 'bytesToHuman'], ['is_safe' => ['html']]),

            new TwigFilter('basename', [AppExtensionRuntime::class, 'fileBasename'], ['is_safe' => ['html']]),
            new TwigFilter('dirname', [AppExtensionRuntime::class, 'fileDirname'], ['is_safe' => ['html']]),
            new TwigFilter('filesize', [AppExtensionRuntime::class, 'fileSize'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            // new TwigFunction('function_name', [AppExtensionRuntime::class, 'doSomething']),
        ];
    }
}
