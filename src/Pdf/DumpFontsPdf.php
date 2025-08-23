<?php

namespace App\Pdf;

/**
 * Ce script montre comment imprimer tous les caractères d'une police en colonnes sur une page. 
 * Les polices Arial, Symbol et ZapfDingbats sont affichées. 
 * Cela peut être utile car elles contiennent de nombreux caractères intéressants : pastilles, flèches, étoiles, téléphones... Par exemple, le caractère chr(41) de ZapfDingbats est une enveloppe.
 */
class DumpFontsPdf extends MyFPDF
{
    protected int $col = 0;

    /**
     * Imprime tous les caractères d'une police
     * 
     * @param string $fontName Nom de la police
     */
    public function dumpFont($fontName, ?bool $addPage = true): self
    {
        if ($addPage) {
            $this->AddPage();
        }

        // Signet
        $this->addBookmark($fontName, 1);
        // Titre
        $this->SetFont('Arial', '', 16);
        $this->Cell(0, 6, $fontName, 0, 1, 'C');
        // Affichage des caractères en colonnes
        $this->SetCol(0);
        for ($i = 32; $i <= 255; $i++) {
            $this->SetFont('Arial', '', 14);
            $this->Cell(12, 5.5, "$i : ");
            $this->SetFont($fontName);
            $this->Cell(0, 5.5, chr($i), 0, 1);
        }
        $this->setCol(0);
        return $this;
    }

    /**
     * Positionnement en haut d'une colonne
     * 
     * @param int $col Numéro de colonne
     */
    private function setCol(int $col)
    {
        $this->col = $col;
        $this->SetLeftMargin(10 + $col * 40);
        $this->SetY(25);
    }

    /**
     * Va à la colonne suivante
     */
    public function AcceptPageBreak(): mixed
    {
        $this->setCol($this->col + 1);
        return false;
    }
}
