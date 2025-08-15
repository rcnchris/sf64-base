<?php

namespace App\Pdf\Trait;

trait BookmarkPdfTrait
{
    /**
     * Signets définis
     */
    protected array $bookmarks = [];

    /**
     * Numéro courant d'objet de type signet
     */
    private int $nBookmarks = 0;

    /**
     * Ajoute un signet
     * @param string $text Texte du signet
     * @param int $level Niveau du signet (0 pour le plus haut niveau, 1 juste en dessous, etc)
     * @param float $y Ordonnée de la destination du signet dans la page. -1 désigne la position courante
     * @param bool $isUTF8 Définit si le titre est encodé en ISO-8859-1 (false) ou en UTF-8 (true)
     */
    public function addBookmark(
        string $txt,
        int $level = 0,
        float $y = 0,
        bool $utf8 = true
    ): self {
        if (!$utf8) {
            $txt = $this->_UTF8encode($txt);
        }
        if ($y == -1) {
            $y = $this->GetY();
        }
        array_push($this->bookmarks, [
            't' => $txt,
            'l' => $level,
            'y' => ($this->h - $y) * $this->k,
            'p' => $this->PageNo()
        ]);

        return $this;
    }

    /**
     * Ajoute une table des matières à partir des signets
     * 
     * @param string $title Titre de la table des matières
     * @param ?bool $addPage Si vrai, une page est ajoutée pour la TOC
     */
    public function addToc(string $title = 'Index', ?bool $addPage = true): self
    {
        if (empty($this->bookmarks)) {
            return $this;
        }

        if ($addPage) {
            $this->AddPage();
        }

        // Index title
        $this->SetFontSize(20);
        $this->Cell(0, 5, $this->convertText($title), 0, 1, 'C');
        $this->SetFontSize(15);
        $this->Ln(10);

        $size = count($this->bookmarks);
        $pageCellSize = $this->GetStringWidth('p. ' . $this->bookmarks[$size - 1]['p']) + 2;
        for ($i = 0; $i < $size; $i++) {
            // Offset
            $level = $this->bookmarks[$i]['l'];
            if ($level > 0) {
                $this->Cell($level * 8);
            }

            // Caption
            $str = mb_convert_encoding($this->bookmarks[$i]['t'], 'ISO-8859-1', 'UTF-8');
            $strSize = $this->GetStringWidth($str);
            $availSize = $this->w - $this->lMargin - $this->rMargin - $pageCellSize - ($level * 8) - 4;
            while ($strSize >= $availSize) {
                $str = substr($str, 0, -1);
                $strSize = $this->GetStringWidth($str);
            }
            $this->Cell($strSize + 2, $this->FontSize + 2, $str);

            // Filling dots
            $w = $this->w - $this->lMargin - $this->rMargin - $pageCellSize - ($level * 8) - ($strSize + 2);
            $nb = $w / $this->GetStringWidth('.');
            $dots = str_repeat('.', (int)$nb);
            $this->Cell($w, $this->FontSize + 2, $dots, 0, 0, 'R');

            // Page number
            $this->Cell($pageCellSize, $this->FontSize + 2, 'p. ' . $this->bookmarks[$i]['p'], 0, 1, 'R');
        }
        return $this;
    }

    /**
     * Appelée par _putresources pour ajouter les signets
     */
    private function putBookmarks(): void
    {
        $nb = count($this->bookmarks);
        if ($nb == 0) {
            return;
        }
        $lru = [];
        $level = 0;
        foreach ($this->bookmarks as $i => $o) {
            if ($o['l'] > 0) {
                $parent = $lru[$o['l'] - 1];
                // Set parent and last pointers
                $this->bookmarks[$i]['parent'] = $parent;
                $this->bookmarks[$parent]['last'] = $i;
                if ($o['l'] > $level) {
                    // Level increasing: set first pointer
                    $this->bookmarks[$parent]['first'] = $i;
                }
            } else {
                $this->bookmarks[$i]['parent'] = $nb;
            }
            if ($o['l'] <= $level && $i > 0) {
                // Set prev and next pointers
                $prev = $lru[$o['l']];
                $this->bookmarks[$prev]['next'] = $i;
                $this->bookmarks[$i]['prev'] = $prev;
            }
            $lru[$o['l']] = $i;
            $level = $o['l'];
        }
        // Outline items
        $n = $this->n + 1;
        foreach ($this->bookmarks as $i => $o) {
            $this->_newobj();
            $this->_put('<</Title ' . $this->_textstring($o['t']));
            $this->_put('/Parent ' . ($n + $o['parent']) . ' 0 R');
            if (isset($o['prev'])) {
                $this->_put('/Prev ' . ($n + $o['prev']) . ' 0 R');
            }
            if (isset($o['next'])) {
                $this->_put('/Next ' . ($n + $o['next']) . ' 0 R');
            }
            if (isset($o['first'])) {
                $this->_put('/First ' . ($n + $o['first']) . ' 0 R');
            }
            if (isset($o['last'])) {
                $this->_put('/Last ' . ($n + $o['last']) . ' 0 R');
            }
            $this->_put(sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]', $this->PageInfo[$o['p']]['n'], $o['y']));
            $this->_put('/Count 0>>');
            $this->_put('endobj');
        }
        // Outline root
        $this->_newobj();
        $this->nBookmarks = $this->n;
        $this->_put('<</Type /Outlines /First ' . $n . ' 0 R');
        $this->_put('/Last ' . ($n + $lru[0]) . ' 0 R>>');
        $this->_put('endobj');
    }
}
