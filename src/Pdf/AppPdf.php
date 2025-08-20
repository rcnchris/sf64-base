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
            x: $this->lMargin,
            y: $this->tMargin,
            link: $this->options->logo_link,
        );

        // Titre
        $this
            ->setCursor($this->lMargin, $this->tMargin)
            ->setFontStyle(style: 'B', size: 14)
            ->print(
                content: $this->options->title,
                h: $this->options->header_height,
                align: $this->options->header_title_align,
            )
            ->setCursor($this->lMargin, $this->getStartContentY())
            ->setFontStyle()
            ->setToolColor();
    }
}
