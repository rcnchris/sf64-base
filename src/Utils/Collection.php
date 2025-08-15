<?php

namespace App\Utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Gère une liste d'éléments
 */
class Collection implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * Données de la collection dans un tableau
     */
    private array $data = [];

    /**
     * Titre de la collection
     */
    private string $collectionTitle = '';

    /**
     * Options de la collection
     */
    private array $collectionOptions = [];

    /** 
     * Options par défaut
     */
    private array $defaultOptions = [
        'separator' => ',',
        'node_separator' => '.',
        'json_flags' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        'to_string_method' => 'toJson',
        'to_file_method' => 'toJson',
        'csvHeader' => true,
        'timezone' => 'Europe/Paris'
    ];

    /**
     * Est-ce que les données ont été définies ?
     */
    private bool $dataDefined = false;

    /**
     * @param mixed $data Données de la collection
     * @param ?string $collectionTitle Titre de la collection
     * @param ?array $options Options de la collection
     */
    public function __construct(mixed $data = null, string $title = '', array $options = [])
    {
        $this
            ->setCollectionTitle($title)
            ->setCollectionOptions($options)
            ->setCollectionData($data);
    }

    /**
     * Retourne la collection sous la forme d'une chaîne de caratères. La méthode spécifiée dans l'option "to_string_method" est utilisée.
     */
    public function __toString(): string
    {
        return $this->{$this->getCollectionOptions('to_string_method')}();
    }

    /**
     * Retourne la valeur de clé de la collection qui correspond à $property
     * @param string $property Nom de la propriété appelée
     */
    public function __get(string $property): mixed
    {
        return $this->get($property);
    }

    /**
     * Retourne la valeur de clé de la collection qui correspond à $method
     * @param string $method Nom de la méthode appelée
     * @param array $args Arguments non utilisés
     */
    public function __call(string $method, array $args): mixed
    {
        return $this->get($method);
    }

    /**
     * Définit le contenu de la collection
     * @param mixed $data Données de la collection
     */
    private function setCollectionData(mixed $data = null): void
    {
        $type = gettype($data);
        if ($type === 'NULL') {
            return;
        }

        switch ($type) {
            case 'array':
                $this->data = $data;
                $this->dataDefined = true;
                break;

            case 'string':
                $this->setDataFromString($data);
                break;

            case 'double':
            case 'integer':
                $this->data[] = $data;
                $this->dataDefined = true;
                break;

            case 'object':
                $this->setDataFromObject($data);
                break;

            default:
                $this->error(
                    sprintf(
                        "Les données n'ont pas pu être définies. Le type \"%s\" ne le permet pas.",
                        $type
                    ),
                    \LogicException::class,
                );
        }
    }

    /**
     * Définit le contenu de la collection à partir d'une chaîne de caractères
     * @param string $data Données de la collection au format chaîne de caractères
     */
    private function setDataFromString(string $data): void
    {
        if (file_exists($data)) {
            $this->setDataFromFilename($data);
        }

        if (!$this->dataDefined && json_validate($data)) {
            $this->setDataFromJson($data);
        }

        if (!$this->dataDefined && strpos($data, $this->getCollectionOptions('separator'))) {
            $this->setCollectionData(
                array_map(function (string $item) {
                    return trim($item);
                }, array_filter(explode($this->getCollectionOptions('separator'), $data)))
            );
        } elseif (!$this->dataDefined) {
            $this->error(
                sprintf(
                    "Les données n'ont pas pu être définies. La chaîne de caractères \"%s\" ne contient pas le séparateur \"%s\"",
                    $data,
                    $this->getCollectionOptions('separator')
                ),
                \LogicException::class,
            );
        }
    }

    /**
     * Définit les données de la collection à partir d'un fichier
     * @param string $filename Nom du fichier
     */
    private function setDataFromFilename(string $filename): void
    {
        $mime = mime_content_type($filename);
        switch ($mime) {
            case 'application/json':
                $this->setDataFromJson(file_get_contents($filename));
                break;
            case 'text/xml':
                $this->setDataFromXml(file_get_contents($filename));
                break;

            default:
                $this->error(sprintf(
                    'Le fichier %s avec le type MIME \"%s\" ne permet pas de définir les données.',
                    $filename,
                    $mime,
                ));
        }
    }

    /**
     * Définit les données de la collection à partir d'une chaîne de caractères au format JSON
     * @param string $json Chaîne de caractères au format JSON
     */
    public function setDataFromJson(string $json): void
    {
        // Nettoyage du JSON
        $json = preg_replace('/[[:cntrl:]]/', '', $json);
        if (json_validate($json)) {
            $this->data = json_decode($json, true);
            $this->dataDefined = true;
        } else {
            $this->error(json_last_error_msg(), \Exception::class, json_last_error());
        }
    }

    /**
     * Définit les données de la collection à partir d'une chaîne de caractères au format XML
     * @param string $xml Chaîne de caractères au format XML
     */
    private function setDataFromXml(string $xml): void
    {
        $this->setCollectionData(json_decode(json_encode(new \SimpleXMLElement($xml, LIBXML_NOCDATA)), true));
    }

    /**
     * Définit les données de la collection à partir d'un objet
     * @param object $object Objet à définir
     */
    private function setDataFromObject(object $object): void
    {
        if ($object instanceof Collection) {
            $this->data = $object->toArray();
        } elseif ($object instanceof \DateTimeInterface) {
            $this->data = [
                'y' => $object->format('Y'),
                'm' => $object->format('m'),
                'd' => $object->format('d'),
                'h' => $object->format('H'),
                'i' => $object->format('i'),
                's' => $object->format('s'),
                'ts' => $object->getTimestamp(),
                'tz' => $object->getTimezone(),
                'offset' => $object->getOffset()
            ];
            $this->dataDefined = true;
        } elseif ($object instanceof \SimpleXMLElement) {
            $this->setDataFromXml($object->asXML());
        } else {
            $data = [];
            foreach (get_mangled_object_vars($object) as $property => $value) {
                $data[$property] = $value;
            }
            if (!empty($data)) {
                $this->data = $data;
                $this->dataDefined = true;
            }

            if (!$this->dataDefined) {
                $this->error(
                    sprintf(
                        "Les données n'ont pas pu être définies. Les objets type \"%s\" ne sont pas pris en compte.",
                        get_class($object)
                    ),
                    \LogicException::class,
                );
            }
        }
    }

    /**
     * Définit une clé et sa valeur dans la collection
     * @param ?mixed $key La clé à définir. Si elle exsite elle est mise à jour sinon elle est créée
     * @param ?mixed $value La valeur à définir
     */
    public function set(mixed $key = null, mixed $value = null): bool
    {
        if (is_null($key) && is_null($value)) {
            return false;
        }
        if (is_null($key)) {
            array_push($this->data, $value);
            return true;
        } else {
            $this->data[$key] = $value;
        }
        return true;
    }

    /**
     * Supprime un clé et ses valeurs de la collection
     * @param mixed $key La position à supprimer.
     */
    public function delete(mixed $key): void
    {
        if ($this->hasKey($key)) {
            unset($this->data[$key]);
        }
    }

    /**
     * Retourne un élément de la collection
     * @param int|string $key Nom de la clé à retourner
     */
    public function get(int|string $key): mixed
    {
        $indexes = explode($this->getCollectionOptions('node_separator'), $key);
        return $this->getValue($indexes, $this->data);
    }

    /**
     * Retourne la valeur d'une clé selon un index de manière récursive
     * @param array $indexes Tableau des index
     * @param mixed $value Valeur
     */
    private function getValue(array $indexes, mixed $value): mixed
    {
        $key = array_shift($indexes);
        if (empty($indexes)) {
            if (!array_key_exists($key, $value)) {
                return null;
            }
            return is_array($value[$key])
                ? new self($value[$key], $this->getCollectionTitle(), $this->getCollectionOptions())
                : $value[$key];
        } else {
            return $this->getValue($indexes, $value[$key]);
        }
    }

    /**
     * Retourne toutes les clés ou un ensemble des clés de la collection dans une nouvelle collection.
     * @link https://www.php.net/manual/fr/function.array-keys.php
     */
    public function keys(): self
    {
        return $this->newSelf(array_keys($this->data), __FUNCTION__);
    }

    /**
     * Vérifie la présence d'une clé dans la collection
     * @param int|string $key Nom de la clé à vérifier
     */
    public function hasKey(int|string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Vérifie la présence d'une valeur dans les valeurs de la collection.
     * @param mixed $value Valeur à vérifier
     */
    public function hasValue(mixed $value): bool
    {
        return in_array($value, $this->data);
    }

    /**
     * Vérifie si la collection est une liste simple d'éléments
     */
    public function isList(): bool
    {
        return array_is_list($this->data);
    }

    /**
     * Vérifie si la collection contient des clés sous forme de chaîne de caractères
     */
    public function isAssociative(): bool
    {
        return $this->isList() === false;
    }

    /**
     * Retourne le type de données de la collection
     */
    public function getType(): string|false 
    {
        return self::getArrayType($this->data);
    }

    /**
     * Vérifie si la collection est vide
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Retourne le nombre d'éléments de premier niveau de la collection
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Retourne la somme des valeurs de la collection
     */
    public function sum(): int|float
    {
        return array_sum($this->data);
    }

    /**
     * Retourne la moyenne des des valeurs de la collection
     */
    public function average(): int|float
    {
        return $this->sum() / $this->count();
    }

    /**
     * Retourne les valeurs de la collection dans une nouvelle collection
     * @link https://www.php.net/manual/fr/function.array-values.php
     */
    public function getValues(): self
    {
        return $this->newSelf(array_values($this->data), __FUNCTION__);
    }

    /**
     * Retourne les valeurs de la collection jointes avec un séparateur
     * @param ?string $sep Séparateur à utiliser
     */
    public function join(?string $sep = null): string
    {
        if (is_null($sep)) {
            $sep = $this->getCollectionOptions('separator');
        }
        return join($sep, $this->data);
    }

    /**
     * Extraie les valeurs d'une clé de la collection dans une nouvelle collection
     * @param int|string $field Nom de la clé d'un item
     * @param int|string|null $key Nom de la clé d'un item qui sera utilisée comme clé des données extraites
     * @link https://www.php.net/manual/fr/function.array-column.php
     */
    public function extract(int|string $field, int|string|null $key = null): self
    {
        return $this->newSelf(array_column($this->data, $field, $key), __FUNCTION__);
    }

    /**
     * Remplace les clés par les valeurs et les valeurs par les clés dans une nouvelle collection
     * @link https://www.php.net/manual/fr/function.array-flip.php
     */
    public function flip(): self
    {
        return $this->newSelf(array_flip($this->data), __FUNCTION__);
    }

    /**
     * Retourne les valeurs de la collection triées par ordre croissant/décroissant dans une nouvelle collection
     * @param ?string $sortOrder Sens du tri (ASC ou DESC)
     * @param ?int $flags Utilisé pour modifier le comportement de tri en utilisant ces valeurs :
     * - SORT_REGULAR - compare les éléments normalement; les détails sont décrits dans la section des opérateurs de comparaison
     * - SORT_NUMERIC - compare les éléments numériquement
     * - SORT_STRING - compare les éléments comme des chaînes de caractères
     * - SORT_LOCALE_STRING - compare les éléments comme des chaînes de caractères, basé sur la locale courante. Ceci utilise la locale, qui peut être changée en utilisant setlocale()
     * - SORT_NATURAL - compare les éléments comme des chaînes de caractères utilisant "l'ordre naturel" comme natsort()
     * - SORT_FLAG_CASE - peut être combiné (OU bit à bit) avec SORT_STRING ou SORT_NATURAL pour trier les chaînes sans tenir compte de la casse
     * @link https://www.php.net/manual/fr/function.sort.php
     * @link https://www.php.net/manual/fr/function.rsort.php
     */
    public function sort(string $sortOrder = 'ASC', int $flags = SORT_REGULAR): self
    {
        $data = $this->data;
        if (!in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
            $sortOrder = 'ASC';
        }
        ($sortOrder === 'ASC') ? sort($data, $flags) : rsort($data, $flags);
        return $this->newSelf($data, __FUNCTION__);
    }

    /**
     * Retourne le premier élément de la collection
     * @link https://www.php.net/manual/fr/function.array-slice.php
     */
    public function first(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }
        $keys = $this->keys()->toArray();
        $firstKey = array_slice($keys, 0, 1);
        return $this->get($firstKey[0]);
    }

    /**
     * Retourne le dernier élément de la collection
     * @link https://www.php.net/manual/fr/function.array-pop.php
     */
    public function last(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }
        $keys = $this->keys()->toArray();
        return $this->get(array_pop($keys));
    }

    /**
     * Retourne $n élément(s) aléatoire(s) de la collection
     * @param ?int $n Nombre d'éléments à retourner
     * @link https://www.php.net/manual/fr/function.array-rand.php
     */
    public function rand(?int $n = 1): mixed
    {
        $keys = array_rand($this->data, $n);
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $rands = [];
        foreach ($keys as $key) {
            $rands[$key] = $this->data[$key];
        }
        $items = $this->newSelf($rands, __FUNCTION__);
        return $n === 1 ? $items->first() : $items;
    }

    /**
     * Retourne les valeurs dédoublées de la collection dans une nouvelle collection
     * @param ?int $flags Mode de comparaison
     * - SORT_REGULAR - compare les éléments normalement (ne modifie pas les types)
     * - SORT_NUMERIC - compare les éléments numériquement
     * - SORT_STRING - compare les éléments comme des chaînes
     * - SORT_LOCALE_STRING - compare les éléments comme des chaînes, suivant la locale courante.
     * @link https://www.php.net/manual/fr/function.array-unique.php
     */
    public function distinct(?int $flags = SORT_STRING): self
    {
        return $this->newSelf(array_unique($this->data, $flags), __FUNCTION__);
    }

    /**
     * Retourne la différence entre les données de $data et les données de la collection dans une nouvelle collection
     * @param mixed $data Données à comparer
     * @link https://www.php.net/manual/fr/function.array-diff.php
     */
    public function diff(mixed $data): self
    {
        if (!is_array($data)) {
            $data = (new self($data, __FUNCTION__, $this->getCollectionOptions()))->toArray();
        }
        return $this->newSelf(array_diff($this->data, $data), __FUNCTION__);
    }

    /**
     * Applique une fonction de rappel sur toutes les valeurs de la collection et retourne le résultat dans une nouvelle collection
     * @param callable $callback Fonction à utiliser
     * @link https://www.php.net/manual/fr/function.array-map.php
     */
    public function map(callable $callback): self
    {
        return $this->newSelf(array_map($callback, $this->data), __FUNCTION__);
    }

    /**
     * Filtre les éléments de la collection grâce à une fonction de rappel et retourne le résultat dans une nouvelle collection
     * @param callable $callback Fonction à utiliser. Le filtre peut s'appliquer sur la clé ou la valeur ou les deux
     * @link https://www.php.net/manual/fr/function.array-filter.php
     */
    public function filter(callable $callback): self
    {
        return $this->newSelf(array_filter($this->data, $callback, ARRAY_FILTER_USE_BOTH), __FUNCTION__);
    }

    /**
     * Retourne la première clé de la collection associée à la valeur
     * @param mixed $value Valeur à chercher
     * @param ?bool $strict Mode de comparaison
     * @link https://www.php.net/manual/fr/function.array-search.php
     */
    public function search(mixed $value, bool $strict = true): int|string|false
    {
        return array_search($value, $this->data, $strict);
    }

    /**
     * Retourne une portion de la collection dans une nouvelle collection
     * @param int $offset 
     * - Si offset n'est pas négatif, la séquence commencera à cette position dans le tableau array. 
     * - Si offset est négatif, la séquence commencera à la position offset, mais en commençant à la fin du tableau array. 
     * @param ?int $length
     * - Si length est fourni et positif, alors la séquence aura jusqu'à autant d'éléments.
     * - Si la collection est plus courte que length, alors seuls les éléments de la collection disponible seront présents. 
     * - Si length est fourni et négatif, alors la séquence exclura autant d'éléments de la fin de la collection. 
     * - Si il est omis, la séquence aura tout depuis la position offset jusqu'à la fin de la collection. 
     * @param ?bool $preserveKeys Préserve les clés si true
     * @link https://www.php.net/manual/fr/function.array-slice.php
     */
    public function slice(int $offset, ?int $length = null, ?bool $preserveKeys = false): self
    {
        return $this->newSelf(array_slice($this->data, $offset, $length, $preserveKeys), __FUNCTION__);
    }

    /**
     * Fusionne les éléments de la collection avec les éléments de $data et retourne le résultat dans une nouvelle collection
     * @param mixed $data Données à fusionner
     * @link https://www.php.net/manual/fr/function.array-merge.php
     */
    public function merge(mixed $data): self
    {
        return $this->newSelf(array_merge($this->data, (new self($data))->toArray()), __FUNCTION__);
    }

    /**
     * Retourne les éléments sous la forme d'un tableau PHP
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Retourne le contenu de la collection au format JSON
     * @param ?int $flags Masque de bit
     * @param ?int $depth Définit la profondeur maximale. Doit être supérieur à zéro. 
     * @link https://www.php.net/manual/fr/function.json-encode.php
     * @link https://www.php.net/manual/fr/json.constants.php
     */
    public function toJson(?int $flags = null, int $depth = 512): string|false
    {
        return json_encode(
            $this->data,
            is_null($flags) ? $this->getCollectionOptions('json_flags') : $flags,
            $depth
        );
    }

    /**
     * Retourne le contenu de la collection dans une chaîne de caractères au format XML
     * @param ?string $root Balise racine
     * @param ?string $item Balise de chaque élément
     * @param ?string $filename
     */
    public function toXml(?string $root = null, ?string $item = null, ?string $filename = null): string
    {
        $root = empty($title) ? 'root' : $title;
        $item = empty($item) ? 'item' : $item;

        $xml = new \SimpleXMLElement("<{$root}/>", LIBXML_NOEMPTYTAG);
        foreach ($this->data as $key => $value) {
            $xml
                ->addChild($item)
                ->addChild($key, $value);
        }
        return $xml->asXML($filename);
    }

    /**
     * Retourne une chaîne de requête en encodage URL
     * @param ?string $separator Le séparateur d'arguments. S'il n'est pas défini ou est null, arg_separator.output est utilisée pour séparer les arguments.
     * @param ?string $numericPrefix Si des indices numériques sont utilisés dans le tableau de base et que numeric_prefix est fourni, il sera utilisé pour préfixer les noms des index pour les éléments du tableau de base seulement. Cela permet de générer des noms de variables valides, si les données sont ensuite décodées par PHP ou une application CGI.
     * @param ?int $encoding Type d'encodage :
     * - Si encoding_type vaut PHP_QUERY_RFC1738, alors l'encodage est effectué conformément à la » RFC 1738 et les espaces du type de média application/x-www-form-urlencoded, qui est impacté par ce choix, seront encodés sous la forme d'un signe plus (+).
     * - Si encoding_type vaut PHP_QUERY_RFC3986, alors l'encodage est effectué conformément à la » RFC 3986, et les espaces seront encodés en signe pourcent (%20).
     * @link https://www.php.net/manual/fr/function.http-build-query.php
     * @link https://www.php.net/manual/fr/ini.core.php#ini.arg-separator.output
     */
    public function toHttpQuery(
        ?string $separator = null,
        string $numericPrefix = '',
        int $encoding = PHP_QUERY_RFC1738
    ): string {
        return http_build_query($this->data, $numericPrefix, $separator, $encoding);
    }

    /**
     * Retourne une chaîne avec les clés comme attributs et les valeurs associées.
     */
    public function toHtmlAttributes(): string
    {
        $attr = [];
        foreach ($this->data as $key => $value) {
            (is_bool($value) && $value === true)
                ? array_push($attr, sprintf('%s', $key))
                : array_push($attr, sprintf('%s="%s"', $key, $value));
        }
        return join(' ', $attr);
    }

    /**
     * Ecrit le contenu de la collection dans un fichier. La méthode spécifiée dans l'option "to_file_method" est utilisée.
     * @param string $filename Nom du fichier à générer
     */
    public function toFile(string $filename): ?\SplFileInfo
    {
        $method = $this->getCollectionOptions('to_file_method');
        file_put_contents($filename, $this->{$method}());
        return new \SplFileInfo($filename);
    }

    /**
     * Retourne le titre de la collection
     */
    public function getCollectionTitle(): string
    {
        return $this->collectionTitle;
    }

    /**
     * Définit le titre de la collection
     * @param ?string $title Titre de la collection
     */
    public function setCollectionTitle(string $title = ''): self
    {
        $this->collectionTitle = $title;
        return $this;
    }

    /**
     * Retourne les options de l'instance ou l'une d'entre elles
     * @param ?string $key Nom de l'option à retourner
     */
    public function getCollectionOptions(?string $key = null): array|string|int|float
    {
        return array_key_exists($key, $this->collectionOptions)
            ? $this->collectionOptions[$key]
            : $this->collectionOptions;
    }

    /**
     * Définit les options de l'instance
     * @param ?array $options Options de la collection
     */
    public function setCollectionOptions(array $options = []): self
    {
        $this->collectionOptions = array_merge($this->defaultOptions, $options);
        return $this;
    }

    /**
     * Retourne une nouvelle collection à partir de $data et des propriétés de la collection courante
     * @param mixed $data Données de la collection à retourner
     * @param string $title Titre de la collection à retourner
     */
    private function newSelf(mixed $data, string $title): self
    {
        return new self(
            $data,
            sprintf('%s : %s', $this->getCollectionTitle(), $title),
            $this->getCollectionOptions()
        );
    }

    /**
     * Soulève une exception
     * @param string $msg Message de l'erreur
     * @param ?string $classname Nom de la classe de l'exception à utiliser
     * @param ?int $code Code de l'erreur
     * @link https://www.php.net/manual/fr/language.exceptions.php
     * @link https://www.php.net/manual/fr/class.throwable.php
     */
    private function error(
        string $msg,
        string $classname = \Exception::class,
        int $code = 1
    ): void {
        throw new $classname($msg, $code);
    }

    /**
     * ArrayAccess
     */

    /**
     * Indique si une position existe
     * @param mixed $offset Une position à vérifier
     * @link http://php.net/manual/fr/arrayaccess.offsetexists.php
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasKey($offset);
    }

    /**
     * Retourne la valeur à la position donnée
     * @param mixed $offset La position à lire
     * @link http://php.net/manual/fr/arrayaccess.offsetget.php
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Assigne une valeur à une position donnée
     * @param mixed $offset La position à laquelle assigner une valeur
     * @param mixed $value La valeur à assigner
     * @link http://php.net/manual/fr/arrayaccess.offsetset.php
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Supprime un élément à une position donnée
     * @param mixed $offset La position à supprimer
     * @link http://php.net/manual/fr/arrayaccess.offsetunset.php
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->delete($offset);
    }

    /**
     * IteratorAggragate
     */

    /**
     * Retourne un itérateur externe ou un objet traversable
     * @link http://php.net/manual/fr/iteratoraggregate.getiterator.php
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Retourne le type de tableau
     * - list : indexé par des nombres
     * - asso : Valeurs associées à des clés en chaîne de caractères
     * - multi : Multidimensionel
     * 
     * @param array $array Tableau à vérifier
     */
    public static function getArrayType(array $array): string|false
    {
        if (empty($array)) {
            return false;
        }
        if (array_is_list($array) && !is_array($array[0])) {
            $type = 'list';
        } elseif (array_is_list($array) && is_array($array[0])) {
            $type = 'multi';
        } else {
            $type = 'asso';
        }
        return $type;
    }
}
