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

    public function highlight(string|array $content, ?string $lang = 'bash'): string
    {
        $rows = [];
        if (is_string($content)) {
            array_push($rows, $content);
        } elseif (is_array($content)) {
            foreach ($content as $row) {
                array_push($rows, $row);
            }
        }
        return sprintf('<pre><code class="%s">%s</code></pre>', $lang, join(PHP_EOL, $rows));
    }
}
