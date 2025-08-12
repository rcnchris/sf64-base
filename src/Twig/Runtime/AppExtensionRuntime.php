<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    /**
     * Filtre qui permet d'obtenir un canvas à partir d'un code hexadécimal d'une couleur
     * 
     * @param ?string $hex Code hexadécimal d'une couleur
     * @param ?int $width Largeur
     * @param ?int $height Hauteur. Si null identique à la largeur.
     */
    public function canvas(?string $hex = null, ?int $width = 30, ?int $height = null): string
    {
        return sprintf(
            '<canvas style="background-color: %s; border-radius: 5px;" width="%s" height="%s"></canvas>',
            is_null($hex) ? '#CCC' : $hex,
            $width,
            is_null($height) ? $width : $height
        );
    }
}
