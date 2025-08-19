<?php

namespace App\Pdf;

use App\Utils\Collection;

class EtiquettePdf extends MyFPDF
{
    /**
     * Formats d'étiquettes prédéfinis
     */
    private array $etiqFormats = [
        'blank' => [
            'size' => 'A4',
            'unit' => 'mm',
            'margin_left' => 10,
            'margin_top' => 10,
            'nx' => 0,
            'ny' => 0,
            'space_x' => 0,
            'space_y' => 0,
            'width' => 0,
            'height' => 0,
            'font-size' => 8
        ],
        'avery' => [
            '5160' => [
                'size' => 'letter',
                'unit' => 'mm',
                'margin_left' => 1.762,
                'margin_top' => 10.7,
                'nx' => 3,
                'ny' => 10,
                'space_x' => 3.175,
                'space_y' => 0,
                'width' => 66.675,
                'height' => 25.4,
                'font-size' => 8
            ],
            '5161' => [
                'size' => 'letter',
                'unit' => 'mm',
                'margin_left' => 0.967,
                'margin_top' => 10.7,
                'nx' => 2,
                'ny' => 10,
                'space_x' => 3.967,
                'space_y' => 0,
                'width' => 101.6,
                'height' => 25.4,
                'font-size' => 8
            ],
            '5162' => [
                'size' => 'letter',
                'unit' => 'mm',
                'margin_left' => 0.97,
                'margin_top' => 20.224,
                'nx' => 2,
                'ny' => 7,
                'space_x' => 4.762,
                'space_y' => 0,
                'width' => 100.807,
                'height' => 35.72,
                'font-size' => 8
            ],
            '5163' => [
                'size' => 'letter',
                'unit' => 'mm',
                'margin_left' => 1.762,
                'margin_top' => 10.7,
                'nx' => 2,
                'ny' => 5,
                'space_x' => 3.175,
                'space_y' => 0,
                'width' => 101.6,
                'height' => 50.8,
                'font-size' => 8
            ],
            '5164' => [
                'size' => 'letter',
                'unit' => 'in',
                'margin_left' => 0.148,
                'margin_top' => 0.5,
                'nx' => 2,
                'ny' => 3,
                'space_x' => 0.2031,
                'space_y' => 0,
                'width' => 4.0,
                'height' => 3.33,
                'font-size' => 12
            ],
            '8600' => [
                'size' => 'letter',
                'unit' => 'mm',
                'margin_left' => 7.1,
                'margin_top' => 19,
                'nx' => 3,
                'ny' => 10,
                'space_x' => 9.5,
                'space_y' => 3.1,
                'width' => 66.6,
                'height' => 25.4,
                'font-size' => 8
            ],
            'L7163' => [
                'size' => 'A4',
                'unit' => 'mm',
                'margin_left' => 5,
                'margin_top' => 15,
                'nx' => 2,
                'ny' => 7,
                'space_x' => 25,
                'space_y' => 0,
                'width' => 99.1,
                'height' => 38.1,
                'font-size' => 9
            ],
            '3422' => [
                'size' => 'A4',
                'unit' => 'mm',
                'margin_left' => 0,
                'margin_top' => 8.5,
                'nx' => 3,
                'ny' => 8,
                'space_x' => 0,
                'space_y' => 0,
                'width' => 70,
                'height' => 35,
                'font-size' => 9
            ],
        ],
        'agipa' => [],
    ];

    private float $etiqMarginLeft = 0; // Marge gauche
    private float $etiqMarginTop = 0; // Marge du haut
    private float $etiqSpaceX = 0; // Espacement horizontal entre deux étiquettes
    private float $etiqSpaceY = 0; // Espacement vertical entre deux étiquettes
    private int $etiqNumberX = 0; // Nombre d'étiquettes en largeur
    private int $etiqNumberY = 0; // Nombre d'étiquettes en hauteur
    private float $etiqWidth = 0; // Largeur d'une étiquette
    private float $etiqHeight = 0; // Hauteur d'une étiquette
    private float $etiqLineHeight = 0; // Hauteur de ligne dans l'étiquette
    private float $etiqPadding = 0; // Padding
    private int $etiqCountX = 0; // Nombre courant d'étiquettes en largeur
    private int $etiqCountY = 0; // Nombre courant d'étiquettes en hauteur

    /**
     * @param string|array $format Nom ou définition d'un format
     * @param ?int $xFirst Position latérale de la première étiquette
     * @param ?int $yFirst Position verticale de la première étiquette
     * @param ?array $options Options du document
     * @param ?array $data Données du document
     */
    public function __construct(
        string|array $format,
        ?int $xFirst = 1,
        ?int $yFirst = 1,
        ?array $options = [],
        ?array $data = []
    ) {

        parent::__construct($options, $data);

        if (is_array($format)) {
            $currentFormat = array_merge($this->getEtiqFormats()->get('blank')->toArray(), $format);
        } else {
            $currentFormat = $this->getEtiqFormats()->get($format);
            if (is_null($currentFormat)) {
                $this->Error(sprintf('Le format d\'étiquette "%s" est inconnu', $format));
            }
        }

        $this->setEtiqFormat($currentFormat);
        $this->SetMargins(0, 0);
        $this->SetAutoPageBreak(false);
        $this->etiqCountX = $xFirst - 2;
        $this->etiqCountY = $yFirst - 1;
    }

    /**
     * Retourne les formats d'étiquettes prédéfinis
     */
    public function getEtiqFormats(): Collection
    {
        return new Collection($this->etiqFormats, "Formats d'étiquettes prédéfinis");
    }

    /**
     * Définit les valeurs pour un format
     * 
     * @param array $format Format à utiliser
     */
    private function setEtiqFormat($format)
    {
        $this->etiqMarginLeft = $this->convertUnit($format['margin_left'], $format['unit']);
        $this->etiqMarginTop = $this->convertUnit($format['margin_top'], $format['unit']);
        $this->etiqSpaceX = $this->convertUnit($format['space_x'], $format['unit']);
        $this->etiqSpaceY = $this->convertUnit($format['space_y'], $format['unit']);
        $this->etiqNumberX = $format['nx'];
        $this->etiqNumberY = $format['ny'];
        $this->etiqWidth = $this->convertUnit($format['width'], $format['unit']);
        $this->etiqHeight = $this->convertUnit($format['height'], $format['unit']);
        $this->etiqSetFontSize($format['font-size']);
        $this->etiqPadding = $this->convertUnit(3, 'mm');
    }

    /**
     * Retourne la hauteur de ligne pour une taille de police
     * 
     * @param int $pt Taille de la police en point
     */
    private function getHeightChars(int $pt): float
    {
        $a = [
            6 => 2,
            7 => 2.5,
            8 => 3,
            9 => 4,
            10 => 5,
            11 => 6,
            12 => 7,
            13 => 8,
            14 => 9,
            15 => 10
        ];
        if (!isset($a[$pt])) {
            $this->Error(sprintf('Taille de police invalide : "%d"', $pt));
        }
        return $this->convertUnit($a[$pt], 'mm');
    }

    // Set the character size
    // This changes the line height too
    private function etiqSetFontSize($pt)
    {
        $this->etiqLineHeight = $this->getHeightChars($pt);
        $this->SetFontSize($pt);
    }

    // Print a label
    public function addEtiquette($text)
    {
        $this->etiqCountX++;
        if ($this->etiqCountX == $this->etiqNumberX) {
            // Row full, we start a new one
            $this->etiqCountX = 0;
            $this->etiqCountY++;
            if ($this->etiqCountY == $this->etiqNumberY) {
                // End of page reached, we start a new one
                $this->etiqCountY = 0;
                $this->AddPage();
            }
        }

        $posX = $this->etiqMarginLeft + $this->etiqCountX * ($this->etiqWidth + $this->etiqSpaceX) + $this->etiqPadding;
        $posY = $this->etiqMarginTop + $this->etiqCountY * ($this->etiqHeight + $this->etiqSpaceY) + $this->etiqPadding;
        $this->SetXY($posX, $posY);
        $this->print(
            content: $text,
            w: $this->etiqWidth - $this->etiqPadding,
            h: $this->etiqLineHeight
        );
    }

    protected function _putcatalog(): void
    {
        parent::_putcatalog();
        $this->_put('/ViewerPreferences <</PrintScaling /None>>');
    }
}
