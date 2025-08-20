<?php

namespace App\Service;

use App\Pdf\AppPdf;
use App\Utils\Images;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PdfService
{
    public function __construct(
        #[Autowire('%app.pdf%')]
        private readonly array $config,
        #[Autowire('%app.docs_dir%')]
        private readonly string $docDir,
    ) {}

    /**
     * Retourne un document PDF de l'application
     * 
     * @param ?array $options Options du document
     * @param ?array $data Données du document
     */
    public function make(?array $options = [], ?array $data = []): AppPdf
    {
        return new AppPdf(
            array_merge($this->getDefaultOptions(), $options),
            array_merge($this->getDefaultData(), $data)
        );
    }

    /**
     * Retourne les options par défaut d'un document PDF de l'application
     */
    private function getDefaultOptions(): array
    {
        $logoFilename = sprintf('%s/logo-pdf.png', dirname($this->config['logo']));
        if (!file_exists($logoFilename)) {
            Images::make($this->config['logo'])
                ->resize(50, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save($logoFilename, 90, 'PNG');
        }
        return [
            'orientation' => $this->config['orientation'],
            'unit' => $this->config['unit'],
            'size' => $this->config['size'],
            'font_family' => $this->config['font'],
            'font_size' => $this->config['font_size'],
            'tmp_dir' => $this->config['tmp_dir'],
            'creator' => $this->config['creator'],
            'keywords' => $this->config['creator'],
            'logo' => $logoFilename,
            'logo_link' => 'https://github.com/rcnchris/sf64-base',
            'text_color' => $this->config['text_color'],
            'draw_color' => $this->config['draw_color'],
            'fill_color' => $this->config['fill_color'],

            'header_height' => 15,
            'header_fill' => false,
            'header_border' => 'B',
            'header_title_align' => 'R',

            'footer_height' => 15,
            'footer_fill' => false,
            'footer_border' => 'T',

            'pagination_enabled' => 'footer',
        ];
    }

    /**
     * Retourne les données par défaut d'un document PDF MCV
     */
    private function getDefaultData(): array
    {
        return [];
    }
}
