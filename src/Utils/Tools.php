<?php

namespace App\Utils;

use Faker\Factory;
use LogicException;
use SimpleXMLElement;

class Tools
{
    /**
     * Convertit une taille en bits en octets
     * 
     * @param int $bits Valeur à convertir
     * @param ?int $round Nombre de décimales
     */
    public static function bytesToHumanSize(int $bits, ?int $round = 0): string
    {
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for ($i = 0; $bits > 1024 && $i < count($sizes) - 1; $i++) {
            $bits /= 1024;
        }
        return sprintf('%s %s', round($bits, $round), $sizes[$i]);
    }

    /**
     * Retourne les parties du nom d'une classe
     * 
     * @param object|string $class Instance d'un objet ou le nom de la classe
     * @param ?bool $shortName Si true, les namespaces sont supprimés
     */
    public static function namespaceSplit(object|string $class, ?bool $shortName = false): array|string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $classNameParts = explode('\\', $class);
        if ($shortName) {
            return array_pop($classNameParts);
        }
        return $classNameParts;
    }

    /**
     * Retourne une couleur aléatoire au format hexadécimal
     * 
     * @param ?bool $safe Si true, couleurs simples
     */
    public static function getRandColor(?bool $safe = false): string
    {
        $faker = Factory::create();
        return $safe ? $faker->safeHexColor() : $faker->hexColor();
    }

    /**
     * Retourne un utilisateur aléatoire dans un tableau associatif
     * 
     * @return array{username: string, email: string, firstname: string, lastname: string, birthday: \DateTime, phone: string, password: string, color: string}
     */
    public static function getRandUser(): array
    {
        $faker = Factory::create();
        return [
            'username' => $faker->userName(),
            'email' => $faker->email(),
            'firstname' => $faker->firstName(),
            'lastname' => $faker->lastName(),
            'birthday' => $faker->dateTimeThisCentury(),
            'phone' => $faker->phoneNumber(),
            'password' => $faker->password(),
            'color' => $faker->hexColor(),
        ];
    }

    /**
     * Convertit une chaîne de caractères de type Daterange (début - fin) en un tableau associatif avec les clés "start" et "end" et les valeurs associées
     * 
     * @param ?string $daterange Chaîne de caractères de type Daterange (début - fin)
     * @param ?string $format Format attendu de $daterange
     * 
     * @return array{start: \DateTimeImmutable, end: \DateTimeImmutable}
     */
    public static function extractDaterange(?string $daterange = null, ?string $format = 'd/m/Y H:i'): array
    {
        if (empty($daterange)) {
            return [];
        }
        $parts = array_map('trim', explode('-', $daterange));
        return [
            'start' => \DateTimeImmutable::createFromFormat($format, trim($parts[0])),
            'end' => \DateTimeImmutable::createFromFormat($format, trim($parts[1])),
        ];
    }

    /**
     * Supprime un dossier et ses sous-dossiers
     * 
     * @param string $path Chemin absolu du dossier à supprimer
     */
    public static function removeDirTree(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$path/$file") ? self::removeDirTree("$path/$file") : unlink("$path/$file");
        }
        return rmdir($path);
    }

    /**
     * Convertit du contenu XML en tableau PHP
     * 
     * @param SimpleXMLElement|string $data Données à convertir
     * @throws LogicException if XML error
     * @throws Exception if file not found or no xml content
     */
    public static function xmlToArray(SimpleXMLElement|string $data): array
    {
        $xml = null;
        if (is_string($data)) {
            if (file_exists($data)) {
                $xml = @simplexml_load_file($data, 'SimpleXMLElement', LIBXML_NOCDATA);
                if (!($xml instanceof SimpleXMLElement)) {
                    throw new LogicException(libxml_get_last_error()->message, 1);
                }
            } else {
                $xml = new \SimpleXMLElement($data, LIBXML_NOCDATA);
            }
        } else {
            $xml = $data;
        }
        return json_decode(json_encode($xml), true);
    }

    /**
     * Convertit une couleur en RGB ou hexadécimal
     * 
     * @param array|string $color Couleur à convertir. 
     * - Si c'est un tableau, RGB vers hexadécimal
     * - Si c'est une chaîne de caractères, hexadécimal vers RGB
     * @param ?bool $rgbAssociative Si true, le tableau de valeurs RGB sera associatif avec les clés "r", "g" et "b" et les valeurs associées.
     */
    public static function convertColor(array|string $color, ?bool $rgbAssociative = false): array|string
    {
        $type = gettype($color);
        switch ($type) {
            case 'array':
                if (count($color) !== 3) {
                    throw new \Exception(sprintf(
                        'Le tableau de couleurs RGB doit contenir trois valeurs. "%d" ont été spécifiées',
                        count($color)
                    ));
                }
                $return = sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
                break;

            case 'string':
                if ($color[0] !== '#') {
                    $color = '#' . $color;
                }
                if (strlen($color) > 7) {
                    throw new \Exception(sprintf('Code hexadécimal : "%s" de mauvaise longueur ou erroné', $color));
                }
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));

                $return = $rgbAssociative ? compact('r', 'g', 'b') : [$r, $g, $b];
                break;
        }

        return $return;
    }
}
