<?php

namespace App\Pdf;

use App\Pdf\Trait\{BookmarkPdfTrait, JoinFilePdfTrait};

class AppPdf extends MyFPDF
{
    use BookmarkPdfTrait, JoinFilePdfTrait;

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

    /**
     * @inheritdoc
     */
    protected function _putresources(): void
    {
        parent::_putresources();

        $this->putBookmarks();

        if (!empty($this->joinedFiles)) {
            $this->putFiles();
        }
    }

    /**
     * @inheritdoc
     */
    protected function _putcatalog(): void
    {
        parent::_putcatalog();

        if (count($this->bookmarks) > 0) {
            $this->_put('/Outlines ' . $this->nBookmarks . ' 0 R');
            $this->_put('/PageMode /UseOutlines');
        }

        if (!empty($this->joinedFiles)) {
            $this->_put('/Names <</EmbeddedFiles ' . $this->nJoinedFile . ' 0 R>>');
            $a = [];
            foreach ($this->joinedFiles as $info) {
                $a[] = $info['n'] . ' 0 R';
            }
            $this->_put('/AF [' . implode(' ', $a) . ']');
            if ($this->options->open_attachment_pane) {
                $this->_put('/PageMode /UseAttachments');
            }
        }
    }
}
