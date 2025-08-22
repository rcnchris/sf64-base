<?php

namespace App\Pdf;

use App\Utils\{Collection, Tools};

class MyFPDF extends \FPDF
{
    /**
     * Options par défaut
     */
    private array $defaultOptions = [
        'orientation' => 'P', // P ou L
        'unit' => 'mm', // Unité du document (pt, mm, cm ou in)
        'size' => 'A4', // Taille des pages (A3, A4, A5, Letter ou Legal)

        'font_family' => 'Arial', // Police
        'font_style' => '', // Chaîne vide normal, B gras, I italique ou U souligné
        'font_size' => 12, // Taille de la police en point

        'margin_top' => 10, // Marge du haut
        'margin_bottom' => 7, // Marge du bas
        'margin_left' => 10, // Marge gauche
        'margin_right' => 10, // Marge droite
        'margin_cell' => 1, // Marge des cellules

        'line_height' => 5, // Hauteur des lignes

        'text_color' => '#000000', // Couleur du texte
        'draw_color' => '#000000', // Couleur des contours (bordure et dessin)
        'fill_color' => '#ecf0f1', // Couleur de remplissage

        'title' => '', // Metas
        'subject' => '', // Metas
        'creator' => '', // Metas
        'author' => '', // Metas
        'keywords' => '', // Metas

        'ensure_page_exists' => true, // S'assure qu'au moins une page existe

        'zoom' => 'fullpage', // fullpage, fullwidth, real, default ou facteur de zoom à utiliser
        'layout' => 'single', // single, continuous, two ou default

        'graduated_grid' => false, // Dessine une grille graduée. Si true l'échelle est de 5, sinon la spécifier en unité du document.
        'graduated_grid_color' => '#ccffff', // Couleur de la grille graduée.
        'graduated_grid_thickness' => .35, // Epaisseur des lignes graduées.
        'graduated_grid_text_color' => '#cccccc', // Couleur des repères textes gradués.

        'logo' => false, // Chemin logo de l'entête
        'logo_link' => false, // URL cliquable sur le logo

        'header_height' => false, // Hauteur de l'entête de page. false pour aucun.
        'header_border' => 0, // Bordure(s) de l'entête de page (0, 1, L, T, R ou B)
        'header_fill' => false, // Remplissage de l'entête de page
        'header_title_align' => 'C', // Alignement du titre dans l'entête

        'footer_height' => false, // Hauteur du pied de page. false pour aucun.
        'footer_border' => 0, // Bordure(s) du pied de page (0, 1, L, T, R ou B)
        'footer_fill' => false, // Remplissage du pied de page

        'pagination_enabled' => false, // Active la pagination (false, header ou footer)
        'pagination_border' => 0, // Bordure de la pagination (0, 1, L, T, R ou B)
        'pagination_fill' => false, // Code hexa ou booléen. Si true, c'est la couleur de remplissage par défaut.
        'pagination_align' => 'R', // Alignement de la pagination (L, C ou R).

        'timezone' => 'Europe/Paris',
        'tmp_dir' => null,

        'watermark' => false,
        'watermark_color' => '#ffc0cb',
    ];

    /**
     * Options définies de l'instance
     */
    protected ?Collection $options;

    /**
     * Données définies de l'instance
     */
    protected ?Collection $data;

    /**
     * Infos de l'instance
     */
    protected ?Collection $infos;

    /**
     * Signets définis
     */
    private array $bookmarks = [];

    /**
     * Numéro courant d'objet de type signet
     */
    private int $nBookmarks = 0;

    /**
     * Liste des fichiers attachés
     */
    private array $joinedFiles = [];

    /**
     * Numéro courant d'objet de type fichier
     */
    private int $nJoinedFile = 0;

    /**
     * Angle de rotation pour la méthode rotate
     */
    private float $angleRotate = 0;

    /**
     * Contenu javascript
     */
    private string $javascript;

    /**
     * Numéro d'objet javascript
     */
    private int $nJavascript;

    /**
     * Tableau des légendes d'un graphique
     */
    private array $chartLegends = [];

    /**
     * Largeur des légéndes d'un graphique
     */
    private float $chartWidthLegend = 0;

    /**
     * Somme des données d'un graphique
     */
    private float $chartSumData = 0;

    /**
     * Nombre de données d'un graphique
     */
    private int $chartCountData = 0;

    /**
     * @param array $options Options du document
     * @param array $data Données du document
     */
    public function __construct(array $options = [], array $data = [])
    {
        $this->setData($data);

        // Fusion des options par défaut avec celles spécifiées
        $this->options = new Collection(
            array_merge($this->defaultOptions, $options),
            'Options du document'
        );

        // Document
        parent::__construct(
            $this->options->orientation,
            $this->options->unit,
            $this->options->size,
        );

        // Page
        if ($this->options->pagination_enabled !== false) {
            $this->AliasNbPages();
        }

        if ($this->options->ensure_page_exists) {
            $this->ensurePageExists();
        }

        $this->SetDisplayMode(
            $this->options->zoom,
            $this->options->layout
        );

        // Marges
        $this
            ->setMargin('top', $this->options->margin_top)
            ->setMargin('bottom', $this->options->margin_bottom)
            ->setMargin('left', $this->options->margin_left)
            ->setMargin('right', $this->options->margin_right)
            ->setMargin('cell', $this->options->margin_cell);

        // Couleurs par défaut des outils draw, fill et text à partir des options
        $this->setToolColor();

        // Propriétés
        $this->SetTitle($this->convertText($this->options->title));
        $this->SetSubject($this->convertText($this->options->subject));
        $this->SetCreator($this->convertText($this->options->creator));
        $this->SetAuthor($this->convertText($this->options->author));
        $this->SetKeywords($this->convertText($this->options->keywords));
    }

    public function __destruct()
    {
        $this->defaultOptions = [];
        $this->options = null;
        $this->data = null;
        $this->infos = null;
    }

    /**
     * En-tête appelée automatiquement par AddPage().
     */
    public function Header(): void
    {
        // Police
        $this->SetFont(
            $this->options->font_family,
            $this->options->font_style,
            $this->options->font_size,
        );

        // Grille graduée
        if ($this->options->graduated_grid !== false) {
            $this->drawGraduatedGrid();
        }

        // Watermark
        if ($this->options->watermark !== false) {
            $this
                ->setFontStyle('Arial', 'B', 50)
                ->setToolColor('text', $this->options->watermark_color)
                ->rotatedText($this->options->watermark, 45, 55, 190);
        }

        if ($this->options->header_height === false) {
            return;
        }

        $this
            ->setCursor($this->lMargin, $this->tMargin)
            ->setToolColor('draw', $this->options->draw_color)
            ->print(
                content: ' ',
                h: $this->options->header_height,
                w: $this->getBodyWidth(),
                border: $this->options->header_border,
                fill: $this->options->header_fill
            );

        // Pagination
        if ($this->options->pagination_enabled === 'header') {
            $this
                ->setCursor($this->lMargin, $this->tMargin)
                ->setFontStyle(style: 'I', size: 8);
            if (is_string($this->options->pagination_fill)) {
                $this->setToolColor('fill', $this->options->pagination_fill);
            } elseif ($this->options->pagination_fill === true) {
                $this->setToolColor('fill');
            }
            $this
                ->print(
                    content: 'Page ' . $this->PageNo() . ' sur {nb}',
                    mode: 'cell',
                    align: $this->options->pagination_align,
                    fill: $this->options->pagination_fill !== false,
                    border: $this->options->pagination_border,
                )
                ->setCursor($this->lMargin, $this->tMargin);
        }

        $this
            ->setFontStyle()
            ->setToolColor();
    }

    /**
     * Pied de page
     * Appelée automatiquement par AddPage() et Close().
     */
    public function Footer(): void
    {
        if ($this->options->footer_height === false) {
            return;
        }

        $this
            ->setCursor($this->lMargin, $this->getStartFooterY())
            ->print(
                content: ' ',
                h: $this->options->footer_height,
                w: $this->getBodyWidth(),
                border: $this->options->footer_border,
                fill: $this->options->footer_fill
            )
            ->setCursor($this->lMargin, $this->getStartFooterY());

        // Pagination
        if ($this->options->pagination_enabled === 'footer') {
            $this
                ->setCursor($this->lMargin, $this->getStartFooterY())
                ->setFontStyle(style: 'I', size: 8);
            if (is_string($this->options->pagination_fill)) {
                $this->setToolColor('fill', $this->options->pagination_fill);
            } elseif ($this->options->pagination_fill === true) {
                $this->setToolColor('fill');
            }
            $this
                ->print(
                    content: 'Page ' . $this->PageNo() . ' sur {nb}',
                    mode: 'cell',
                    align: $this->options->pagination_align,
                    fill: $this->options->pagination_fill !== false,
                    border: $this->options->pagination_border,
                )
                ->setCursor($this->lMargin, $this->getStartFooterY());
        }
    }

    /**
     * S'assure qu'une page existe
     */
    protected function ensurePageExists(): self
    {
        if ($this->getTotalPages() === 0) {
            $this->AddPage(
                $this->options->orientation,
                $this->options->size,
                $this->options->rotation
            );
        }
        return $this;
    }

    /**
     * Retourne l'ordonnée de départ du contenu du document
     */
    public function getStartContentY(): float
    {
        return $this->tMargin
            + ($this->options->header_height === false ? 0 : $this->options->header_height)
            + $this->options->line_height;
    }

    /**
     * Retourne l'ordonnée de départ du pied de page
     */
    public function getStartFooterY(): float
    {
        return $this->GetPageHeight()
            - ($this->bMargin + ($this->options->footer_height === false ? 0 : $this->options->footer_height));
    }

    /**
     * Ajoute une page et retourne l'instance
     * @param ?string $orientation P ou L
     * @param ?string $size Type de page 
     * - A3
     * - A4
     * - A5
     * - Letter
     * - Legal
     * ou bien d'un tableau contenant la largeur et la hauteur (exprimées en unité utilisateur).
     * @param ?float $rotation Angle de rotation de la page. La valeur doit être un multiple de 90 et la rotation s'effectue dans le sens horaire.
     * @param ?float $y Définir l'ordonnée
     */
    public function newPage(
        ?string $orientation = '',
        ?string $size = '',
        ?float $rotation = 0,
        ?float $y = null
    ): self {
        $this->AddPage($orientation, $size, $rotation);
        if (!is_null($y)) {
            $this->SetY($y);
        }
        return $this;
    }

    /**
     * Ajoute un saut de ligne de hauteur $h et retourne l'instance
     * @param ?float $h Hauteur du saut de ligne
     */
    public function addLn(?float $h = null): self
    {
        $this->Ln($h);
        return $this;
    }

    /**
     * Définit la couleur d'un outil
     * @param string $tool Nom de l'outil (text, draw, fill ou all) à colorer
     * @param ?string $hexa Code hexadécimal d'une couleur. Si non renseigné, c'est la couleur des options de l'instance qui s'applique.
     */
    public function setToolColor(string $tool = 'all', ?string $hexa = null): self
    {
        $tools = ['text', 'draw', 'fill', 'all'];
        if (!in_array($tool, $tools)) {
            $msg = sprintf(
                "Le nom de l'outil à colorer est incorrect : \"%s\". Seules les valeurs \"%s\" sont acceptées.",
                $tool,
                join(', ', $tools)
            );
            $this->Error($msg);
        }

        if (is_null($hexa) && $tool !== 'all') {
            $hexa = $this->options->get($tool . '_color');
        }

        switch ($tool) {
            case 'text':
                list($r, $g, $b) = $this->convertColor($hexa);
                $this->SetTextColor($r, $g, $b);
                break;
            case 'draw':
                list($r, $g, $b) = $this->convertColor($hexa);
                $this->SetDrawColor($r, $g, $b);
                break;
            case 'fill':
                list($r, $g, $b) = $this->convertColor($hexa);
                $this->SetFillColor($r, $g, $b);
                break;
            default:
                list($r, $g, $b) = $this->convertColor(is_null($hexa) ? $this->options->text_color : $hexa);
                $this->SetTextColor($r, $g, $b);
                list($r, $g, $b) = $this->convertColor(is_null($hexa) ? $this->options->draw_color : $hexa);
                $this->SetDrawColor($r, $g, $b);
                list($r, $g, $b) = $this->convertColor(is_null($hexa) ? $this->options->fill_color : $hexa);
                $this->SetFillColor($r, $g, $b);
                break;
        }
        return $this;
    }

    /**
     * Définit le style de la police
     * @param string $family Nom de la police
     * @param string $style Style de la police ('', B, I ou U)
     * @param int $size Taille de la police en point
     */
    public function setFontStyle(string $family = '', string $style = '', int $size = 0): self
    {
        $this->SetFont(
            family: empty($family) ? $this->options->font_family : $family,
            style: empty($style) ? $this->options->font_style : $style,
            size: ($size === 0) ? $this->options->font_size : $size
        );
        return $this;
    }

    /**
     * Définit la valeur d'un type de marge
     * @param string $type Nom de la marge
     * @param float $value Valeur de la marge en unité du document
     */
    public function setMargin(string $type, float $value): self
    {
        switch ($type) {
            case 'top':
                $this->SetTopMargin($value);
                break;
            case 'left':
                $this->SetLeftMargin($value);
                break;
            case 'right':
                $this->SetRightMargin($value);
                break;
            case 'bottom':
                $this->bMargin = $value;
                break;
            case 'cell':
                $this->cMargin = $value;
                break;
            default:
                $this->Error(sprintf("Le type \"%s\" no correspond pas à un type de marge. Utiliser seulement top, bottom, left, right ou cell.", $type));
        }
        return $this;
    }

    /**
     * Retourne le nombre total de pages du document
     */
    public function getTotalPages(): int
    {
        return count($this->pages);
    }

    /**
     * Retourne la date de création du document s'il a été rendu.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        if ($this->state === 3) {
            return (new \DateTimeImmutable())
                ->setTimestamp($this->CreationDate)
                ->setTimezone(new \DateTimeZone($this->options->timezone));
        }
        return null;
    }

    /**
     * Retourne la position courante du curseur dans un tableau
     * @param ?float $x Abscisse (position horizontale)
     * @param ?float $y Ordonnée (position verticale)
     * @param bool $associative Si true le tableau retournée contient les clés "x" et "y"
     */
    public function getCursor(
        ?float $x = null,
        ?float $y = null,
        ?bool $associative = false
    ): array {
        $x = is_null($x) ? $this->GetX() : $x;
        $y = is_null($y) ? $this->GetY() : $y;
        return $associative ? compact('x', 'y') : [$x, $y];
    }

    /**
     * Définit la position du curseur et retourne l'instance
     * 
     * @param ?float $x Abscisse du curseur
     * @param ?float $y Ordonnée du curseur
     */
    public function setCursor(?float $x = null, ?float $y = null): self
    {
        $this->SetXY(
            is_null($x) ? $this->GetX() : $x,
            is_null($y) ? $this->GetY() : $y
        );
        return $this;
    }

    /**
     * Retourne toutes les marges dans une collection
     */
    public function getMargins(): Collection
    {
        return new Collection([
            'left' => $this->lMargin,
            'right' => $this->rMargin,
            'top' => $this->tMargin,
            'bottom' => $this->bMargin,
            'cell' => $this->cMargin,
        ], 'Marges du document PDF');
    }

    /**
     * Retourne la largeur utile de la page (sans les marges)
     */
    public function getBodyWidth(): float
    {
        return parent::GetPageWidth() - ($this->lMargin + $this->rMargin);
    }

    /**
     * Retourne la hauteur utile de la page (sans les marges)
     */
    public function getBodyHeight(): float
    {
        return parent::GetPageHeight() - ($this->tMargin + $this->bMargin);
    }

    /**
     * Retourne la valeur du milieu pour le type spécifié
     * @param string $type x ou y
     */
    public function getMiddleOf(string $type): float
    {
        $middle = 0;
        switch ($type) {
            case 'x':
                $middle = $this->GetPageWidth() / 2;
                break;

            case 'y':
                $middle = $this->GetPageHeight() / 2;
                break;

            default:
                $this->Error('Le type doit être "x" ou "y"');
        }
        return $middle;
    }

    /**
     * Retourne les métas dans une collection
     */
    public function getMetas(): Collection
    {
        return new Collection($this->metadata, "Métas données du document PDF");
    }

    /**
     * Retourne les options dans une collection
     */
    public function getOptions(): ?Collection
    {
        return $this->options;
    }

    /**
     * Retourne les données dans une collection
     */
    public function getData(): ?Collection
    {
        return $this->data;
    }

    /**
     * Définit les données du document
     * @param array $data Données dans un tableau
     */
    public function setData(array $data = []): self
    {
        $this->data = new Collection($data, 'Données du document PDF');
        return $this;
    }

    /**
     * Convertit une valeur dans l'unité du document
     * 
     * @param float|int $value Valeur à convertir
     * @param string $src Unité de la valeur ('in', 'mm' ou 'cm')
     */
    public function convertUnit(float|int $value, string $src)
    {
        $dest = $this->options->unit;
        if ($src != $dest) {
            $a['in'] = 39.37008;
            $a['mm'] = 1000;
            $a['cm'] = 100;
            return $value * $a[$dest] / $a[$src];
        } else {
            return $value;
        }
    }

    /**
     * Convertit de l'UTF-8 en Windows-1252 si nécessaire
     * @param string $text Texte à convertir
     */
    public function convertText(?string $text = null): string
    {
        return Tools::convertText($text, 'UTF-8', 'Windows-1252');
    }

    /**
     * Convertit une couleur en RGB ou hexadécimal
     * 
     * @param array|string $color Couleur à convertir. 
     * - Si c'est un tableau, RGB vers hexadécimal
     * - Si c'est une chaîne de caractères, hexadécimal vers RGB
     * @param ?bool $rgbAssociative Si true, le tableau de valeurs RGB sera associatif avec les clés "r", "g" et "b" et les valeurs associées.
     */
    public function convertColor(array|string $color, ?bool $rgbAssociative = false): array|string
    {
        return Tools::convertColor($color, $rgbAssociative);
    }

    /**
     * Ecrit sur le document PDF
     * 
     * @param mixed $content Contenu à écrire
     * @param ?float $h Hauteur de la ligne à écrire
     * @param ?float $w Largeur de la ligne à écrire
     * @param int|string $border Bordure (0, 1, L, R, T, B)
     * @param ?string $align Aligement du contenu (L, R, J ou C)
     * @param ?int $ln Positionnement après écriture (0, 1 ou 2).
     * @param ?bool $fill Remplissage pour les modes cell et multi
     * @param ?string $link Lien pour les modes text et cell
     * @param ?bool $wAuto Limiter la largeur de la cellule à la taille du contenu  pour les modes cell et multi
     * @param ?string $mode Mode d'écriture (text, cell ou multi)
     */
    public function print(
        mixed $content,
        ?float $h = null,
        ?float $w = 0,
        int|string $border = 0,
        ?string $align = 'L',
        ?int $ln = 0,
        ?bool $fill = false,
        ?string $link = '',
        ?bool $wAuto = false,
        ?string $mode = 'multi',
    ): self {

        if (empty($content)) {
            return $this;
        }

        // Hauteur de la ligne
        if (is_null($h)) {
            $h = $this->options->line_height;
        }
        switch ($mode) {
            case 'text':
                if (is_string($content) || is_numeric($content)) {
                    $this->Write(
                        h: $h,
                        txt: $this->convertText($content),
                        link: $link
                    );
                } elseif (is_object($content) && method_exists($content, '__toString')) {
                    $content = (string) $content;
                    $this->Write(
                        h: $h,
                        txt: $this->convertText($content),
                        link: $link
                    );
                } elseif (is_array($content) && !empty($content)) {
                    foreach ($content as $row) {
                        $this->Write(
                            h: $h,
                            txt: $this->convertText($row),
                            link: $link
                        );
                    }
                }
                break;

            case 'cell':
                if (is_string($content) || is_numeric($content)) {
                    $content = $this->convertText($content);
                    $this->Cell(
                        w: $wAuto ? $this->GetStringWidth($content) : $w,
                        h: $h,
                        txt: $content,
                        border: $border,
                        ln: $ln,
                        align: $align,
                        fill: $fill,
                        link: $link
                    );
                } elseif (is_object($content) && method_exists($content, '__toString')) {
                    $content = $this->convertText((string)$content);
                    $this->Cell(
                        w: $wAuto ? $this->GetStringWidth($content) : $w,
                        h: $h,
                        txt: $content,
                        border: $border,
                        ln: $ln,
                        align: $align,
                        fill: $fill,
                        link: $link
                    );
                } elseif (is_array($content) && !empty($content)) {
                    foreach ($content as $row) {
                        $row = $this->convertText($row);
                        $this->Cell(
                            w: $wAuto ? $this->GetStringWidth($row) : $w,
                            h: $h,
                            txt: $row,
                            border: $border,
                            ln: $ln,
                            align: $align,
                            fill: $fill,
                            link: $link
                        );
                    }
                }
                break;

            case 'multi':
                if (is_string($content) || is_numeric($content)) {
                    $content = $this->convertText($content);
                    $this->MultiCell(
                        w: $wAuto ? $this->GetStringWidth($content) : $w,
                        h: $h,
                        txt: $content,
                        border: $border,
                        align: $align,
                        fill: $fill
                    );
                } elseif (is_object($content) && method_exists($content, '__toString')) {
                    $content = $this->convertText((string)$content);
                    $this->MultiCell(
                        w: $wAuto ? $this->GetStringWidth($content) : $w,
                        h: $h,
                        txt: $content,
                        border: $border,
                        align: $align,
                        fill: $fill
                    );
                } elseif (is_array($content) && !empty($content)) {
                    if (array_is_list($content)) {
                        foreach ($content as $row) {
                            $row = $this->convertText($row);
                            $this->MultiCell(
                                w: $wAuto ? $this->GetStringWidth($row) : $w,
                                h: $h,
                                txt: $row,
                                border: $border,
                                align: $align,
                                fill: $fill
                            );
                        }
                    } else {
                        foreach ($content as $key => $value) {
                            $value = $this->convertText($value);
                            $this
                                ->setFontStyle(style: 'B')
                                ->print(sprintf('%s : ', $key), mode: 'cell', wAuto: true)
                                ->setFontStyle()
                                ->MultiCell(
                                    w: $wAuto ? $this->GetStringWidth($value) : $w,
                                    h: $h,
                                    txt: $value,
                                    border: $border,
                                    align: $align,
                                    fill: $fill
                                );
                        }
                    }
                }
                break;

            default:
                $this->Error(sprintf(
                    "Le mode \"%s\" n'est pas géré dans la méthode \"%s\" de la classe \"%s\".",
                    $mode,
                    __FUNCTION__,
                    __CLASS__
                ));
        }
        return $this;
    }

    /**
     * Ecrit du code source
     * 
     * @apram string|array $code Code source à écrire
     */
    public function printCode(string|array $code): self
    {
        return $this
            ->setFontStyle(family: 'courier', size: 8)
            ->print($code, fill: true)
            ->setFontStyle();
    }

    /**
     * Imprime une liste à puces
     * 
     * @param array $data Données de la liste
     */
    public function printBulletArray(array $data, ?int $level = 0): self
    {
        $bullet = chr(149);
        $xStart = $this->lMargin + $level;
        foreach ($data as $key => $value) {
            $this->SetX($xStart);
            if (is_string($key)) {
                $this->Cell($this->GetStringWidth($bullet . ' '), $this->options->line_height, $bullet . ' ');
                $this->print($key);
            }
            if (is_array($value)) {
                $this->printBulletArray($value, $level + 5);
            } else {
                $this->Cell($this->GetStringWidth($bullet . ' '), $this->options->line_height, $bullet . ' ');
                $this->print($value);
            }
        }
        return $this;
    }

    /**
     * Retourne les informations du document dans une collection
     */
    public function getInfos(): Collection
    {
        $vars = array_keys(get_object_vars($this));

        $infos = [
            'Titre' => $this->metadata['Title'],
            'Sujet' => $this->metadata['Subject'],
            'Créateur' => $this->metadata['Creator'],
            'Auteur' => $this->metadata['Author'],
            'Mots clés' => $this->metadata['Keywords'],
            'Orientation' => $this->CurOrientation,
            'Unité' => $this->options->unit,
            'Taille' => $this->options->size,
            'Fuseau horaire' => $this->options->timezone,
            'Zoom' => $this->options->zoom,
            'Layout' => $this->options->layout,
            'Pages' => $this->getTotalPages(),
            'Facteur d\'échelle' => $this->k,
            'Largeur' => $this->w,
            'Hauteur' => $this->h,
            'Marge haut' => $this->tMargin,
            'Marge bas' => $this->bMargin,
            'Marge gauche' => $this->lMargin,
            'Marge droite' => $this->rMargin,
            'Marge cellule' => $this->cMargin,
            'Hauteur ligne texte' => $this->options->line_height,
            'Epaisseur ligne' => $this->LineWidth,
            'Largeur corps' => $this->getBodyWidth(),
            'Hauteur corps' => $this->getBodyHeight(),
            'Police' => $this->FontFamily,
            'Polices' => join(', ', $this->CoreFonts),
            'Rotation' => empty($this->CurRotation) ? 'Aucune' : $this->CurRotation,
            'Données' => $this->data->count(),
            'Images' => count($this->images),
            'Fichiers' => count($this->joinedFiles),
            'Signets' => count($this->bookmarks),
            'Javascript' => in_array('javascript', $vars) ? 'Oui' : 'Non',
            'Version PHP' => PHP_VERSION,
            'Version FPDF' => $this->metadata['Producer'],
            'Classe PDF' => get_class($this),
            'Parents' => join(', ', array_map(function (string $name) {
                return Tools::namespaceSplit($name, true);
            }, class_parents($this))),
            // 'Traits' => join(', ', array_map(function (string $name) {
            //     return Tools::namespaceSplit($name, true);
            // }, class_uses($this))),
            'Fichier classe' => __FILE__,
        ];

        return new Collection($infos, 'Informations du document PDF');
    }

    /**
     * Imprime la liste des informations du document
     * 
     * @param ?bool $addPage Si vrai, une page est ajoutée pour les informations
     * @param ?bool $addBookmark Ajouter un signet
     */
    public function printInfos(?bool $addPage = false, ?bool $addBookmark = false): self
    {
        $infos = $this->getInfos();
        $this
            ->setToolColor()
            ->setFontStyle('Arial', 'B', 12);

        if ($addPage) {
            $this->AddPage();
        }

        // Calcul de la taille de la colonne des labels
        $maxWidth = 0;
        foreach ($infos->keys() as $key) {
            $width = $this->GetStringWidth($this->convertText($key)) + $this->cMargin;
            if ($maxWidth < $width) {
                $maxWidth = $width;
            }
        }

        // Titre
        if ($addBookmark) {
            $this->addBookmark('Informations', 1);
        }
        $this
            ->setCursor($this->GetX(), $this->tMargin + $this->options->header_height + 5)
            ->setFontStyle(style: 'BU', size: 18)
            ->print('Informations sur le document', align: 'C')
            ->Ln(3);

        // Ecrit toutes les informations
        foreach ($infos as $name => $value) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell($maxWidth, 6, $this->convertText($name), 1, 0, 'C', true);
            $this->SetFont('Courier', '', 10);
            $this->Cell(0, 6, $this->convertText($value), 1, 1);
        }

        // Fichiers attachés
        if ($infos->Fichiers > 0) {
            $this->Ln(5);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, $this->convertText('Fichier(s) attaché(s)'), 1, 1, 'C', true);

            // Entêtes
            $this->SetFont('Arial', '', 10);
            $this->Cell(50, 8, 'Nom', 1, 0, 'C');
            $this->Cell(120, 8, 'Fichier', 1, 0, 'C');
            $this->Cell(0, 8, 'Taille', 1, 1, 'C');

            // Lignes
            foreach ($this->{'joinedFiles'} as $file) {
                $this->Cell(50, 6, $file['name'], 1, 0, 'C');
                $this->Cell(120, 6, $file['file'], 1);
                $this->Cell(0, 6, Tools::bytesToHumanSize($file['size'], 2), 1, 1, 'C');
            }
        }

        return $this
            ->setToolColor()
            ->setFontStyle();
    }

    /**
     * Envoie le document vers une destination donnée en fonction du type demandé
     * 
     * @param string $type Envoie le document vers une destination donnée en fonction du type demandé :
     * - I : navigateur
     * - D : télécharger en utilisant $name pour le nom du fichier
     * - F : fichier en utilisant $name pour le nom du fichier
     * - S : chaîne de caractères 
     * @param string $name Le nom du fichier. Il est ignoré si le type est "S".
     * @param bool $isUtf8 Indique si $name est encodé en ISO-8859-1 (false) ou en UTF-8 (true). Ce paramètre est utilisé uniquement pour les types "I" et "D".
     */
    public function render(string $type = 'I', string $name = 'doc.pdf', bool $isUtf8 = false): string
    {
        if (in_array($type, ['F', 'D']) && !is_dir(dirname($name))) {
            mkdir(dirname($name));
        }
        return $this->Output($type, $name, $isUtf8);
    }

    /**
     * Dessine une ligne
     * 
     * @param ?float $xStart Abscisse de départ
     * @param ?float $yStart Ordonnée de départ
     * @param ?float $xEnd Abscisse de fin
     * @param ?float $yEnd = Ordonnée de fin
     */
    public function drawLine(
        ?float $xStart = null,
        ?float $yStart = null,
        ?float $xEnd = null,
        ?float $yEnd = null
    ): self {
        $xStart = $xStart ?? $this->lMargin;
        $yStart = $yStart ?? $this->GetY();
        $xEnd = $xEnd ?? $this->GetPageWidth() - $this->rMargin;
        $yEnd = $yEnd ?? $this->GetY();

        $this->Line($xStart, $yStart, $xEnd, $yEnd);
        return $this;
    }

    /**
     * Dessine une grille graduée.
     * Si l'option "graduated_grid" est true, l'échelle est de 5, sinon la spécifier en unité du document.
     */
    public function drawGraduatedGrid(): self
    {
        $spacing = is_bool($this->options->graduated_grid)
            ? 5
            : (is_numeric($this->options->graduated_grid) ? $this->options->graduated_grid : 5);

        $this
            ->setToolColor('draw', $this->options->graduated_grid_color)
            ->SetLineWidth($this->options->graduated_grid_thickness);
        for ($i = 0; $i < $this->w; $i += $spacing) {
            $this->Line($i, 0, $i, $this->h);
        }
        for ($i = 0; $i < $this->h; $i += $spacing) {
            $this->Line(0, $i, $this->w, $i);
        }
        $this->setToolColor('draw');
        list($x, $y) = $this->getCursor();

        $this
            ->setToolColor('text', $this->options->graduated_grid_text_color)
            ->SetFont('Arial', 'I', 8);
        for ($i = 20; $i < $this->h; $i += 20) {
            $this->SetXY(1, $i - 3);
            $this->Write(4, $i);
        }
        for ($i = 20; $i < (($this->w) - ($this->rMargin) - 10); $i += 20) {
            $this->SetXY($i - 1, 1);
            $this->Write(4, $i);
        }

        $this->SetXY($x, $y);

        return $this->setToolColor();
    }

    /**
     * Ajoute un signet
     * 
     * @param string $text Texte du signet
     * @param int $level Niveau du signet (0 pour le plus haut niveau, 1 juste en dessous, etc)
     * @param float $y Ordonnée de la destination du signet dans la page. -1 désigne la position courante
     * @param bool $isUTF8 Définit si le titre est encodé en ISO-8859-1 (false) ou en UTF-8 (true)
     */
    public function addBookmark(
        string $txt,
        int $level = 0,
        float $y = -1,
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

    /**
     * Ajoute un fichier à la liste des fichiers
     * 
     * @param string $filename Chemin absolu du fichier
     * @param ?string $name Nom du fichier
     * @param ?string $desc Description du fichier
     */
    public function addFile(string $filename, ?string $name = '', ?string $desc = ''): self
    {
        if (!file_exists($filename)) {
            $this->Error('Fichier introuvable : ' . $filename);
        }
        array_push($this->joinedFiles, [
            'file' => $filename,
            'name' => (empty($name)) ? basename($filename) : $name,
            'size' => filesize($filename),
            'desc' => $desc,
        ]);
        return $this;
    }

    /**
     * Appelée par _putresources pour ajouter les fichiers joints
     */
    private function putFiles(): void
    {
        foreach ($this->joinedFiles as $i => &$info) {
            $file = $info['file'];
            $name = $info['name'];
            $desc = $info['desc'];

            $fc = file_get_contents($file);
            $size = strlen($fc);
            $date = @date('YmdHisO', filemtime($file));
            $md = 'D:' . substr($date, 0, -2) . "'" . substr($date, -2) . "'";;

            $this->_newobj();
            $info['n'] = $this->n;
            $this->_put('<<');
            $this->_put('/Type /Filespec');
            $this->_put('/F (' . $this->_escape($name) . ')');
            $this->_put('/UF ' . $this->_textstring($name));
            $this->_put('/EF <</F ' . ($this->n + 1) . ' 0 R>>');
            if ($desc) {
                $this->_put('/Desc ' . $this->_textstring($desc));
            }
            $this->_put('/AFRelationship /Unspecified');
            $this->_put('>>');
            $this->_put('endobj');

            $this->_newobj();
            $this->_put('<<');
            $this->_put('/Type /EmbeddedFile');
            $this->_put('/Subtype /application#2Foctet-stream');
            $this->_put('/Length ' . $size);
            $this->_put('/Params <</Size ' . $size . ' /ModDate ' . $this->_textstring($md) . '>>');
            $this->_put('>>');
            $this->_putstream($fc);
            $this->_put('endobj');
        }
        unset($info);

        $this->_newobj();
        $this->nJoinedFile = $this->n;
        $a = [];
        foreach ($this->joinedFiles as $i => $info) {
            $a[] = $this->_textstring(sprintf('%03d', $i)) . ' ' . $info['n'] . ' 0 R';
        }
        $this->_put('<<');
        $this->_put('/Names [' . join(' ', $a) . ']');
        $this->_put('>>');
        $this->_put('endobj');
    }

    /**
     * Effectue une rotation autour d'un centre donné
     * 
     * @param float $angle Angle de rotation
     * @param ?float $x Abscisse de départ
     * @param ?float $y Ordonnée de départ
     */
    private function rotate(float $angle, ?float $x = null, ?float $y = null): self
    {
        $this->setCursor($x, $y);
        if ($this->angleRotate != 0) {
            $this->_out('Q');
        }
        $this->angleRotate = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
        return $this;
    }

    /**
     * Effectue une rotation sur du texte
     * 
     * @param string $txt Texte à faire pivoter
     * @param float $angle Angle de rotation
     * @param ?float $x Abscisse de départ
     * @param ?float $y Ordonnée de départ
     */
    public function rotatedText(string $txt, float $angle, ?float $x = null, ?float $y = null): self
    {
        // Rotation du texte autour de son origine
        $this->rotate($angle, $x, $y);
        $this->print($txt, mode: 'text');
        return $this->rotate(0);
    }

    /**
     * Effectue une rotation sur une image
     * 
     * @param mixed $file Fichier de l'image à faire pivoter
     * @param float $angle Angle de rotation
     * @param ?float $x Abscisse de départ
     * @param ?float $y Ordonnée de départ
     * @param ?float $w Largeur de l'image
     * @param ?float $h Hauteur de l'image
     */
    public function rotatedImage(
        mixed $file,
        float $angle,
        ?float $x = null,
        ?float $y = null,
        ?float $w = 0,
        ?float $h = 0
    ): self {
        // Rotation de l'image autour du coin supérieur gauche
        $this->rotate($angle, $x, $y);
        $this->Image($file, $x, $y, $w, $h);
        return $this->rotate(0);
    }

    /**
     * Retourne la valeur de output pour un style
     * 
     * @param string $style Style de dessin, comme pour Rect (D, F ou FD)
     */
    private function defineOutputStyle(?string $style = 'D'): string
    {
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        return $op;
    }

    /**
     * Dessine une ellipse
     * 
     * @param float $x Abscisse du centre
     * @param float $y Ordonnée du centre
     * @param float $rx Rayon horizontal
     * @param float $ry Rayon vertical
     * @param string $style Style de dessin, comme pour Rect (D, F ou FD)
     */
    public function ellipsis(float $x, float $y, float $rx, float $ry, ?string $style = 'D'): self
    {
        list($x, $y) = $this->setCursor($x, $y)->getCursor();
        $op = $this->defineOutputStyle($style);
        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
        $k = $this->k;
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $rx) * $k,
            ($h - $y) * $k,
            ($x + $rx) * $k,
            ($h - ($y - $ly)) * $k,
            ($x + $lx) * $k,
            ($h - ($y - $ry)) * $k,
            $x * $k,
            ($h - ($y - $ry)) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $lx) * $k,
            ($h - ($y - $ry)) * $k,
            ($x - $rx) * $k,
            ($h - ($y - $ly)) * $k,
            ($x - $rx) * $k,
            ($h - $y) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $rx) * $k,
            ($h - ($y + $ly)) * $k,
            ($x - $lx) * $k,
            ($h - ($y + $ry)) * $k,
            $x * $k,
            ($h - ($y + $ry)) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x + $lx) * $k,
            ($h - ($y + $ry)) * $k,
            ($x + $rx) * $k,
            ($h - ($y + $ly)) * $k,
            ($x + $rx) * $k,
            ($h - $y) * $k,
            $op
        ));
        return $this;
    }

    /**
     * Dessine un cercle
     * 
     * @param float $x Abscisse du centre
     * @param float $y Ordonnée du centre
     * @param float $r Rayon
     * @param string $style Style de dessin, comme pour Rect (D, F ou FD)
     */
    public function circle(float $x, float $y, float $r, ?string $style = 'D'): self
    {
        return $this->ellipsis($x, $y, $r, $r, $style);
    }

    /**
     * Dessine un secteur de cercle
     * 
     * @param float $xc Abscisse du centre
     * @param float $yc Ordonnée du centre
     * @param float $r Rayon
     * @param float $a Angle de début en degré
     * @param float $b Angle de fin en degré
     * @param ?string $style Style de dessin, comme pour Rect (D, F ou FD)
     * @param ?bool $cw Indique si le sens est celui des aiguilles d'une montre
     * @param ?int $o Origine des angles (0 à droite, 90 en haut, 180 à gauche, 270 en bas)
     */
    public function sector(
        float $xc,
        float $yc,
        float $r,
        float $a,
        float $b,
        ?string $style = 'FD',
        ?bool $cw = true,
        ?int $o = 90
    ): self {
        $d0 = $a - $b;
        if ($cw) {
            $d = $b;
            $b = $o - $a;
            $a = $o - $d;
        } else {
            $b += $o;
            $a += $o;
        }
        while ($a < 0) {
            $a += 360;
        }
        while ($a > 360) {
            $a -= 360;
        }
        while ($b < 0) {
            $b += 360;
        }
        while ($b > 360) {
            $b -= 360;
        }
        if ($a > $b) {
            $b += 360;
        }
        $b = $b / 360 * 2 * M_PI;
        $a = $a / 360 * 2 * M_PI;
        $d = $b - $a;
        if ($d == 0 && $d0 != 0) {
            // @codeCoverageIgnoreStart
            $d = 2 * M_PI;
            // @codeCoverageIgnoreEnd
        }
        $k = $this->k;
        $hp = $this->h;
        if (sin($d / 2)) {
            $MyArc = 4 / 3 * (1 - cos($d / 2)) / sin($d / 2) * $r;
        } else {
            $MyArc = 0;
        }
        $this->_out(sprintf('%.2F %.2F m', ($xc) * $k, ($hp - $yc) * $k));
        $this->_out(sprintf('%.2F %.2F l', ($xc + $r * cos($a)) * $k, (($hp - ($yc - $r * sin($a))) * $k)));
        if ($d < M_PI / 2) {
            $this->drawArc(
                $xc + $r * cos($a) + $MyArc * cos(M_PI / 2 + $a),
                $yc - $r * sin($a) - $MyArc * sin(M_PI / 2 + $a),
                $xc + $r * cos($b) + $MyArc * cos($b - M_PI / 2),
                $yc - $r * sin($b) - $MyArc * sin($b - M_PI / 2),
                $xc + $r * cos($b),
                $yc - $r * sin($b)
            );
        } else {
            $b = $a + $d / 4;
            $MyArc = 4 / 3 * (1 - cos($d / 8)) / sin($d / 8) * $r;
            $this->drawArc(
                $xc + $r * cos($a) + $MyArc * cos(M_PI / 2 + $a),
                $yc - $r * sin($a) - $MyArc * sin(M_PI / 2 + $a),
                $xc + $r * cos($b) + $MyArc * cos($b - M_PI / 2),
                $yc - $r * sin($b) - $MyArc * sin($b - M_PI / 2),
                $xc + $r * cos($b),
                $yc - $r * sin($b)
            );
            $a = $b;
            $b = $a + $d / 4;
            $this->drawArc(
                $xc + $r * cos($a) + $MyArc * cos(M_PI / 2 + $a),
                $yc - $r * sin($a) - $MyArc * sin(M_PI / 2 + $a),
                $xc + $r * cos($b) + $MyArc * cos($b - M_PI / 2),
                $yc - $r * sin($b) - $MyArc * sin($b - M_PI / 2),
                $xc + $r * cos($b),
                $yc - $r * sin($b)
            );
            $a = $b;
            $b = $a + $d / 4;
            $this->drawArc(
                $xc + $r * cos($a) + $MyArc * cos(M_PI / 2 + $a),
                $yc - $r * sin($a) - $MyArc * sin(M_PI / 2 + $a),
                $xc + $r * cos($b) + $MyArc * cos($b - M_PI / 2),
                $yc - $r * sin($b) - $MyArc * sin($b - M_PI / 2),
                $xc + $r * cos($b),
                $yc - $r * sin($b)
            );
            $a = $b;
            $b = $a + $d / 4;
            $this->drawArc(
                $xc + $r * cos($a) + $MyArc * cos(M_PI / 2 + $a),
                $yc - $r * sin($a) - $MyArc * sin(M_PI / 2 + $a),
                $xc + $r * cos($b) + $MyArc * cos($b - M_PI / 2),
                $yc - $r * sin($b) - $MyArc * sin($b - M_PI / 2),
                $xc + $r * cos($b),
                $yc - $r * sin($b)
            );
        }
        $this->_out($this->defineOutputStyle($style));
        return $this;
    }

    /**
     * Dessine un rectangle avec les coins arrondis
     * 
     * @param float $w Largeur du rectangle
     * @param float $h Hauteur du rectangle
     * @param ?float $r Rayon de l'arc
     * @param ?string $corners Numéro du ou des angles à arrondir : 1, 2, 3, 4 ou toute combinaison (1=haut gauche, 2=haut droite, 3=bas droite, 4=bas gauche).
     * @param ?float $x Abscisse du coin supérieur gauche du rectangle
     * @param ?float $y Ordonnée du coin supérieur gauche du rectangle
     * @param ?string $style Style de dessin, comme pour Rect (D, F ou FD)
     */
    public function roundedRect(
        float $w,
        float $h,
        ?float $r = 5,
        ?string $corners = '1234',
        ?float $x = null,
        ?float $y = null,
        ?string $style = 'D'
    ): self {
        list($x, $y) = $this->setCursor($x, $y)->getCursor();
        $k = $this->k;
        $hp = $this->h;
        $op = $this->defineOutputStyle($style);
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        if (strpos($corners, '2') === false) {
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $y) * $k));
        } else {
            $this->drawArc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        }

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        if (strpos($corners, '3') === false) {
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - ($y + $h)) * $k));
        } else {
            $this->drawArc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        }

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        if (strpos($corners, '4') === false) {
            $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - ($y + $h)) * $k));
        } else {
            $this->drawArc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        }

        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        if (strpos($corners, '1') === false) {
            $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $y) * $k));
            $this->_out(sprintf('%.2F %.2F l', ($x + $r) * $k, ($hp - $y) * $k));
        } else {
            $this->drawArc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        }
        $this->_out($op);
        return $this;
    }

    /**
     * Dessine un arc
     */
    private function drawArc($x1, $y1, $x2, $y2, $x3, $y3): void
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c ',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

    /**
     * Dessine un polygone
     * 
     * @param array $points Liste des points du polygone
     * @param string $style Style de dessin, comme pour Rect (D, F ou FD)
     */
    public function polygon($points, ?string $style = 'D'): self
    {
        $op = $this->defineOutputStyle($style);
        $h = $this->h;
        $k = $this->k;

        $strPoints = '';
        for ($i = 0; $i < count($points); $i += 2) {
            $strPoints .= sprintf('%.2F %.2F', $points[$i] * $k, ($h - $points[$i + 1]) * $k);
            $strPoints .= ($i == 0) ? ' m ' : ' l ';
        }
        $this->_out($strPoints . $op);
        return $this;
    }

    /**
     * Déssine un graphique de type camembert
     * 
     * @param array $data Données du graphique dans un tableau associatif contenant les libellés et les données correspondantes
     * @param float $x Abscisse du centre
     * @param float $y Ordonnée du centre
     * @param float $r Rayon
     * @param string $format Format à utiliser pour afficher les légendes. Il s'agit dune chaîne pouvant contenir les valeurs spéciales suivantes : %l (libellé), %v (valeur) et %p (pourcentage).
     * @param ?int $decimals Décimales des valeurs
     * @param array $colors Tableau contenant les couleurs. S'il n'est pas indiqué, un dégradé de gris sera utilisé.
     */
    public function chartPie(
        array $data,
        float $x,
        float $y,
        ?float $r = 10,
        ?string $format = '%l (%p)',
        ?int $decimals = 2,
        ?array $colors = [],
    ): self {

        $this->setChartLegends($data, $format, $decimals);

        // Couleurs
        if (empty($colors)) {
            for ($i = 0; $i < $this->chartCountData; $i++) {
                $gray = $i * intval(255 / $this->chartCountData);
                $colors[$i] = $this->convertColor([$gray, $gray, $gray]);
            }
        }

        // Secteurs
        $this->SetLineWidth(0.2);
        $angleStart = 0;
        $angleEnd = 0;
        $i = 0;
        foreach ($data as $val) {
            $angle = ($val * 360) / floatval($this->chartSumData);
            if ($angle != 0) {
                $angleEnd = $angleStart + $angle;
                $this
                    ->setToolColor('fill', $colors[$i])
                    ->sector($x, $y, $r, $angleStart, $angleEnd);
                $angleStart += $angle;
            }
            $i++;
        }

        // Légendes
        $xStartLegend = $x + $r + 5;
        $yStartLegend = $y - $r;
        $this
            ->setCursor($xStartLegend, $yStartLegend)
            ->setFontStyle(style: '', size: 10);
        for ($i = 0; $i < $this->chartCountData; $i++) {
            $this->setToolColor('fill', $colors[$i]);
            $yLegend = $yStartLegend + ($i * $this->options->line_height);
            $this->Rect(
                $xStartLegend,
                $yLegend,
                $this->options->line_height,
                $this->options->line_height,
                'DF'
            );
            $this
                ->setCursor($xStartLegend + 5, $yLegend)
                ->print($this->chartLegends[$i], mode: 'cell');
        }
        $this
            ->addLn(5)
            ->SetX($xStartLegend);

        return $this
            ->setFontStyle(style: 'B')
            ->print(sprintf('Total : %s', number_format($this->chartSumData, $decimals, ',', ' ')));
    }

    /**
     * Dessine un graphique de type histogramme
     * 
     * @param array $data Données du graphique dans un tableau associatif contenant les libellés et les données correspondantes
     * @param ?float $x Abscisse du coin supérieur gauche
     * @param ?float $y Ordonnée du coin supérieur gauche
     * @param ?float $w Largeur max du graphique
     * @param ?float $h Hauteur max du graphique
     * @param ?string $format Format à utiliser pour afficher les légendes. Il s'agit dune chaîne pouvant contenir les valeurs spéciales suivantes : %l (libellé), %v (valeur) et %p (pourcentage).
     * @param ?int $decimals Décimales des valeurs
     * @param ?string $bgColor Couleur de fond du graphique
     * @param ?string $barColor Couleur des barres
     * @param ?float $maxScaleVal Valeur haute de l'échelle. Prend par défaut la valeur maximale des données.
     * @param ?int $nbScales Nombre de subdivisions de l'échelle (4 par défaut).
     */
    public function chartBar(
        array $data,
        ?float $x = null,
        ?float $y = null,
        ?float $w = null,
        ?float $h = 70,
        ?string $format = '%l : %v (%p)',
        ?int $decimals = 2,
        ?string $bgColor = '#ecf0f1',
        ?string $barColor = '#7f8c8d',
        ?float $maxScaleVal = 0,
        ?int $nbScales = 4
    ) {
        $this
            ->setFontStyle(style: '', size: 8)
            ->setChartLegends($data, $format, $decimals);

        list($xSection, $ySection) = $this->getCursor($x, $y);

        $marge = 2;
        $yChart = $ySection + $marge;
        $hChart = floor($h - $marge * 2);
        $xChart = $xSection + $marge * 2 + $this->chartWidthLegend;
        if (is_null($w)) {
            $w = $this->getBodyWidth();
        }
        $wChart = floor($w - $marge * 3 - $this->chartWidthLegend);
        if ($maxScaleVal == 0) {
            $maxScaleVal = max($data);
        }
        $valIndRepere = ceil($maxScaleVal / $nbScales);
        $maxScaleVal = $valIndRepere * $nbScales;
        $wScale = floor($wChart / $nbScales);
        $wChart = $wScale * $nbScales;
        $unit = $wChart / $maxScaleVal;
        $hBar = floor($hChart / ($this->chartCountData + 1));
        $hChart = $hBar * ($this->chartCountData + 1);
        $spaceBar = floor($hBar * 80 / 100);

        // Dessine le contour du graphique
        $styleChart = 'D';
        if (!empty($bgColor)) {
            $styleChart .= 'F';
            $this->setToolColor('fill', $bgColor);
        }
        $this->SetLineWidth(0.2);
        $this->Rect($xChart, $yChart, $wChart, $hChart, $styleChart);

        // Dessine les barres du graphique
        $this->setToolColor('fill', $barColor);
        $i = 0;
        foreach ($data as $val) {
            // Barre
            $wBar = (int)($val * $unit);
            $yBar = $yChart + ($i + 1) * $hBar - $spaceBar / 2;
            $this->Rect($xChart, $yBar, $wBar, $spaceBar, 'DF');

            // Légende
            $this
                ->setCursor(0, $yBar)
                ->print($this->chartLegends[$i], w: $xChart - $marge, h: $spaceBar, align: 'R');
            $i++;
        }

        // Echelles
        for ($i = 0; $i <= $nbScales; $i++) {
            $xScale = $xChart + $wScale * $i;
            $this->Line($xScale, $yChart, $xScale, $yChart + $hChart);
            $val = $i * $valIndRepere;
            $xScale = $xChart + $wScale * $i - $this->GetStringWidth($val) / 2;
            $yScale = $yChart + $hChart - $marge;
            $this->Text($xScale, $yScale, $val);
        }
    }

    /**
     * Définit les légendes d'un graphique
     * 
     * @param array $data Données du graphique dans un tableau associatif contenant les libellés et les données correspondantes
     * @param ?string $format Format à utiliser pour afficher les légendes. Il s'agit dune chaîne pouvant contenir les valeurs spéciales suivantes : %l (libellé), %v (valeur) et %p (pourcentage).
     * @param ?int $decimals Décimales des valeurs
     */
    private function setChartLegends(
        array $data,
        ?string $format = '%l : %v',
        ?int $decimals = 2,
    ): self {
        $this->chartLegends = [];
        $this->chartWidthLegend = 0;
        $this->chartSumData = array_sum($data);
        $this->chartCountData = count($data);
        foreach ($data as $l => $val) {
            $p = sprintf('%.2f', $val / $this->chartSumData * 100) . '%';
            $legend = str_replace(
                ['%l', '%v', '%p'],
                [$l, number_format($val, $decimals, ',', ' '), $p],
                $format
            );
            $this->chartLegends[] = $legend;
            $this->chartWidthLegend = max($this->GetStringWidth($legend), $this->chartWidthLegend);
        }
        return $this;
    }

    /**
     * Ouvre la boîte d'impression à l'ouverture du document (ne fonctionne pas avec Chrome). 
     */
    public function autoPrint(): self
    {
        return $this->addJavascript('print(true);');
    }

    /**
     * Ajout du contenu javascript au document
     * 
     * @param string $javascript Contenu à ajouter
     */
    public function addJavascript(string $script): self
    {
        $this->javascript = $script;
        return $this;
    }

    /**
     * Appelée par _putresources pour ajouter le code javascript
     */
    private function putJavascript(): void
    {
        $this->_newobj();
        $this->nJavascript = $this->n;
        $this->_put('<<');
        $this->_put('/Names [(EmbeddedJS) ' . ($this->n + 1) . ' 0 R]');
        $this->_put('>>');
        $this->_put('endobj');
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/S /JavaScript');
        $this->_put('/JS ' . $this->_textstring($this->javascript));
        $this->_put('>>');
        $this->_put('endobj');
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

        if (!empty($this->javascript)) {
            $this->putJavascript();
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
            $this->_put('/PageMode /UseAttachments');
        }

        if (!empty($this->javascript)) {
            $this->_put('/Names <</JavaScript ' . ($this->nJavascript) . ' 0 R>>');
        }
    }

    /**
     * @inheritdoc
     */
    protected function _endpage()
    {
        $this->angleRotate = 0;
        $this->_out('Q');
        parent::_endpage();
    }
}
