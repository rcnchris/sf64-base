<?php

namespace App\Pdf;

class AppPdf extends MyFPDF
{
    public function Header(): void
    {
        parent::Header();

        // Logo
        $this->Image(
            file: $this->options->logo,
            x: $this->options->margin_left,
            y: $this->options->margin_top,
            link: $this->options->logo_link,
        );

        // Titre
        $this
            ->setToolColor('text', $this->options->draw_color)
            ->setFontStyle(style: 'B', size: 14)
            ->print(
                content: $this->options->title,
                h: 15,
                align: 'R',
            )
            ->setToolColor('text');

        $this->setCursor($this->lMargin, $this->getStartContentY());
    }
}
