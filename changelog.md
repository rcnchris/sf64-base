# Création projet

```bash
composer create-project symfony/skeleton:"6.4.*" sf64-base
cd sf64-base && code .
```

-----

## Initialisation Git

Créer le repository sf64-base sur Github

```bash
git init
git remote add origin git@github.com:rcnchris/sf64-base.git
git branch -M main
git add --all
git commit -m "Création projet"
git push origin main
```

Config **composer**

```json
"name": "rcnchris/sf64-base",
"description": "Projet Symfony 6.4.* de base",
"version": "1.0.0",
"config": {
    "allow-plugins": {
        "php-http/discovery": true,
        "symfony/flex": true,
        "symfony/runtime": true
    },
    "sort-packages": true,
    "process-timeout": 0
},
```

Scripts **composer**

```json
"sfy": "php bin/console",
"sf-about": "@sfy about",
"commit": "git add --all && git commit -m",
"git-log": "git log --oneline"
```

-----

## Installation des librairies

```bash
composer require webapp symfony/apache-pack symfony/ux-icons twig/intl-extra twig/html-extra twig/cssinliner-extra twig/string-extra twig/inky-extra twig/markdown-extra league/commonmark symfony/ux-live-component intervention/image:"^2.7"
rm -f compose.yaml && rm -f compose.override.yaml
composer remove symfony/ux-turbo
composer require --dev fakerphp/faker doctrine/doctrine-fixtures-bundle
```

Les trois dernières lignes permettent de forcer le https

```apache
# Fichier public/.htaccess
RewriteCond %{HTTP:Authorization} .+
RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteCond %{HTTPS} !on
RewriteRule ^(.*) https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

-----

## Paramètres

```yaml
# Fichier config/services.yaml
parameters:
    app.name: "Projet Symfony 6.4.* de base"
    app.locale: fr
    app.locales: en|fr|es
    app.country: FR
    app.locale_country: '%app.locale%_%app.country%'
    app.timezone: 'Europe/Paris'
    app.public_dir: '%kernel.project_dir%/public'
    app.docs_dir: '%app.public_dir%/docs'
    app.docs_path: '/docs'
    app.assets_dir: '%kernel.project_dir%/assets'
    app.logo: '%app.assets_dir%/images/logo.png'
    app.logo_path: 'images/logo.png'
    app.tmp_dir: '%kernel.project_dir%/var/tmp'
```

### Traductions

```yaml
# Fichier config/packages/translation.yaml
framework:
    default_locale: '%app.locale%'
    translator:
        default_path: '%kernel.project_dir%/translations'
        providers:
```

### Doctrine auto_mapping

```yaml
# Fichier config/packages/doctrine.yaml
orm:
    controller_resolver:
        auto_mapping: false
```

### UX Icônes
```bash
touch config/packages/ux_icons.yaml
```

```yaml
# Fichier config/packages/ux_icons.yaml
ux_icons:
    icon_dir: '%app.assets_dir%/icons'
    default_icon_attributes:
        fill: currentColor
        aria-hidden: true
        width: '16'
        height: '16'
        class: 'mb-1'
    aliases:
        activity: bi:activity
        book: bi:book
        category: bi:inboxes
        chart-line: bi:graph-up
        chart-bar: bi:bar-chart-line-fill
        chart-pie: bi:pie-chart-fill
        close: bi:x-square-fill
        danger: bi:radioactive
        dashboard: bi:speedometer2
        delete: bi:trash
        event: bi:calendar-event
        eye: bi:eye
        filter: bi:funnel
        filter-f: bi:funnel-fill
        gear: bi:gear
        fingerprint: bi:fingerprint
        hierarchy: bi:diagram-3
        home: bi:house
        info: bi:info-circle-fill
        info-circle: bi:info-circle
        list: bi:card-list
        login: bi:box-arrow-in-right
        logout: bi:box-arrow-right
        mail: bi:envelope
        mysql: logos:mysql
        new: bi:plus-circle
        pdf: bi:file-earmark-pdf
        phone: bi:telephone
        php: logos:php
        pointer: bi:hand-index-thumb
        profile: bi:person-vcard-fill
        register: bi:person-fill-add
        reload: bi:arrow-clockwise
        save: bi:save
        search: bi:search
        send: bi:send-fill
        success: bi:check-circle-fill
        table: bi:table
        user: bi:person
        users: bi:people
        watch: bi:watch
```

### Twig

```yaml
# Fichier config/packages/twig.yaml
twig:
    file_name_pattern: '*.twig'
    form_themes: 
        - 'bootstrap_5_layout.html.twig'
        - 'form/form.html.twig'

    globals:
        app_name: '%app.name%'
        app_logo_path: '%app.logo_path%'

    paths:
        '%app.assets_dir%/images': images
        '%app.assets_dir%/styles': styles

when@test:
    twig:
        strict_variables: true
```

### Mailer

```yaml
# Fichier config/packages/mailer.yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
        envelope:
            sender: 'no-reply@sf64-base.fr'
        headers:
            From: '%app.name% <no-reply@sf64-base.fr>'
            X-Custom-Header: '%app.name%'
```

### Messenger

```yaml
# Fichier config/packages/messenger.yaml
framework:
    messenger:
        failure_transport: failed

        serializer:
            default_serializer: messenger.transport.symfony_serializer
            symfony_serializer:
                format: json
                context: { }

        transports:
            # https://symfony.com/doc/current/messenger.html#transport-configuration
            async:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    use_notify: true
                    check_delayed_interval: 60000
                retry_strategy:
                    max_retries: 3
                    multiplier: 2
            failed: 'doctrine://default?queue_name=failed'
            sync: 'sync://'

        default_bus: messenger.bus.default

        buses:
            messenger.bus.default: []

        routing:
            Symfony\Component\Mailer\Messenger\SendEmailMessage: sync
            Symfony\Component\Notifier\Message\ChatMessage: async
            Symfony\Component\Notifier\Message\SmsMessage: async
```

Scripts **composer**

```json
"sf-env": "@sfy debug:dotenv",
"sf-trans": "@sfy translation:extract fr --format=yaml --force",
"sf-icons": [
    "@sfy ux:icons:lock --force",
    "@sfy ux:icons:warm-cache"
],
"sf-assets": "@sfy asset-map:compile",
"to-prod": [
    "@sf-trans",
    "@sf-icons",
    "@sf-assets",
    "composer dump-env prod",
    "composer install --no-dev --optimize-autoloader"
],
"to-dev": [
    "rm -rf public/assets",
    "composer dump-env dev",
    "composer install --optimize-autoloader"
],
```

-----

## HomeController

```bash
php bin/console make:controller Home
```

```php
// Fichier src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app.', methods: ['GET'])]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app.home', [], Response::HTTP_FOUND);
    }

    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('home/home.html.twig', [
            'title' => __FUNCTION__,
        ]);
    }
}
```

```twig
{# Fichier templates/home/home.html.twig #}
{% block title %}
	{{ title|trans|capitalize }}
	-
	{{ parent() }}
{% endblock %}

{% block body %}
{{ ux_icon('home', {width: '20', height: '20'}) }}
{{ 'welcome on'|trans|capitalize }} {{ app_name }}
{{ 'hello'|trans|capitalize }}
{% endblock %}
```

```bash
composer sf-trans
```

```yaml
# Fichier translations/messages+intl-icu.fr.yaml
hello: bonjour
home: accueil
'welcome on': 'bienvenue sur'
```

-----

## Assets

### CSS

```css
/** Fichier assets/styles/app.css */
@charset "UTF-8";

:root {
    --color-pomegranate: #c0392b;
    --color-silver: #bdc3c7;
    --color-asphalt: #34495e;
}

body {
    background: linear-gradient(
        120deg,
        var(--color-silver),
        var(--color-asphalt)
    );
    min-height: 100vh;
    min-height: -webkit-fill-available;
    font-family: sans-serif;
}

label.required:before {
    content: '*';
    color: var(--color-pomegranate);
}

code {
    border-radius: 3px;
}
```

### Bootstrap

```bash
php bin/console importmap:require bootstrap
mkdir assets/js
touch assets/js/twbs.js
```

```js
// Fichier assets/js/twbs.js
import 'bootstrap/dist/css/bootstrap.min.css';
import { Tooltip, Toast, Popover } from "bootstrap";

// Tooltip
const tooltipList = [].slice.call(
    document.querySelectorAll('[data-toggle="tooltip"]')
);
tooltipList.map((e) => {
    return new Tooltip(e);
});

// Toast
const toastList = [].slice.call(document.querySelectorAll(".toast"));
toastList.map((e) => {
    var toast = new Toast(e, { delay: 4000 });
    toast.show();
});

// Popover
const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
);
const popoverList = [...popoverTriggerList].map((e) => new Popover(e));
```

### Highlight

```bash
php bin/console importmap:require highlight.js/lib/core
php bin/console importmap:require highlight.js/lib/languages/apache 
php bin/console importmap:require highlight.js/lib/languages/bash 
php bin/console importmap:require highlight.js/lib/languages/css 
php bin/console importmap:require highlight.js/lib/languages/javascript 
php bin/console importmap:require highlight.js/lib/languages/json 
php bin/console importmap:require highlight.js/lib/languages/makefile 
php bin/console importmap:require highlight.js/lib/languages/php 
php bin/console importmap:require highlight.js/lib/languages/sql 
php bin/console importmap:require highlight.js/lib/languages/twig 
php bin/console importmap:require highlight.js/lib/languages/yaml
php bin/console importmap:require highlight.js/styles/agate.css 
```

```bash
touch assets/js/highlight.js
```

```js
// Fichier assets/js/highlight.js
import hljs from 'highlight.js/lib/core';
import apache from 'highlight.js/lib/languages/apache';
import bash from 'highlight.js/lib/languages/bash';
import css from 'highlight.js/lib/languages/css';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import makefile from 'highlight.js/lib/languages/makefile';
import php from 'highlight.js/lib/languages/php';
import sql from 'highlight.js/lib/languages/sql';
import twig from 'highlight.js/lib/languages/twig';
import yaml from 'highlight.js/lib/languages/yaml';
import 'highlight.js/styles/agate.css';

hljs.registerLanguage('apache', apache);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('css', css);
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('json', json);
hljs.registerLanguage('makefile', makefile);
hljs.registerLanguage('php', php);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('twig', twig);
hljs.registerLanguage('yaml', yaml);
hljs.highlightAll();
```

### Fichier assets/app.js

```js
import './bootstrap.js'
import $ from "jquery";
window.$ = $;
import './js/highlight.js'
import './js/twbs.js'
import './styles/app.css'
```

-----

## Environnement de développement

```
# Fichier .env.dev
DATABASE_URL="mysql://root:@127.0.0.1:3306/sf64-base?serverVersion=8.0.30&charset=utf8mb4"
MAILER_DSN=smtp://10.0.0.21:1025
```

### Base de données

Scripts **composer**

```json
"db-create": "@sfy doctrine:database:create --if-not-exists",
"db-drop": "@sfy doctrine:database:drop --force --if-exists",
"db-migration": "@sfy doctrine:migrations:diff --formatted -n",
"db-migrate": "@sfy doctrine:migrations:migrate -n",
"db-schema": "@sfy doctrine:schema:create -q -n",
"db-meta": "@sfy doctrine:migrations:sync-metadata-storage",
"db-load": "@sfy doctrine:fixtures:load -n",
"db-init": [
    "@db-drop",
    "@db-create",
    "@db-migrate",
    "@db-load --append"
],
"db-save": "mysqldump -u root sf64-base > ./var/dump/sf64-base.sql",
"db-restore": "mysql -u root sf64-base < ./var/dump/sf64-base.sql",
```

```bash
mkdir var/dump
composer db-init
composer db-save
```

-----

## Doctrine

### Extension beberlei/doctrineextensions

```bash
composer require beberlei/doctrineextensions
```

```yaml
# Fichier config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        profiling_collect_backtrace: '%kernel.debug%'
        use_savepoints: true
    orm:
        auto_generate_proxy_classes: true
        enable_lazy_ghost_objects: true
        report_fields_where_declared: true
        validate_xml_mapping: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        controller_resolver:
            auto_mapping: false
        mappings:
            App:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
        dql:
            datetime_functions:
                NOW: DoctrineExtensions\Query\Mysql\Now
                DAY: DoctrineExtensions\Query\Mysql\Day
                MONTH: DoctrineExtensions\Query\Mysql\Month
                YEAR: DoctrineExtensions\Query\Mysql\Year
                DATE_FORMAT: DoctrineExtensions\Query\Mysql\DateFormat
                TIME_DIFF: DoctrineExtensions\Query\Mysql\TimeDiff
                TIME_TO_SEC: DoctrineExtensions\Query\Mysql\TimeToSec
                SEC_TO_TIME: DoctrineExtensions\Query\Mysql\SecToTime
            numeric_functions:
                RAND: DoctrineExtensions\Query\Mysql\Rand
            string_functions:
                CAST: DoctrineExtensions\Query\Mysql\Cast
                GROUP_CONCAT: DoctrineExtensions\Query\Mysql\GroupConcat
                IFNULL: DoctrineExtensions\Query\Mysql\IfNull
                JSON_EXTRACT: App\Query\Mysql\JsonExtract

when@test:
    doctrine:
        dbal:
            dbname_suffix: '_test%env(default::TEST_TOKEN)%'

when@prod:
    doctrine:
        orm:
            auto_generate_proxy_classes: false
            proxy_dir: '%kernel.build_dir%/doctrine/orm/Proxies'
            query_cache_driver:
                type: pool
                pool: doctrine.system_cache_pool
            result_cache_driver:
                type: pool
                pool: doctrine.result_cache_pool

    framework:
        cache:
            pools:
                doctrine.result_cache_pool:
                    adapter: cache.app
                doctrine.system_cache_pool:
                    adapter: cache.system
```

#### Query JSON_EXTRACT

```bash
mkdir -p src/Query/Mysql
touch src/Query/Mysql/JsonExtract.php
```

```php
// Fichier src/Query/Mysql/JsonExtract.php
namespace App\Query\Mysql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\{Parser, SqlWalker, TokenType};

/**
 * JsonExtract ::= "JSON_EXTRACT" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class JsonExtract extends FunctionNode
{
    public $field = null;
    public $key = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->field = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->key = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'JSON_UNQUOTE(JSON_EXTRACT(%s, %s))',
            $this->field->dispatch($sqlWalker),
            $this->key->dispatch($sqlWalker),
        );
    }
}
```

### Extension stof/doctrine-extensions-bundle

```bash
composer require stof/doctrine-extensions-bundle
```

```yaml
# Fichier config/packages/stof_doctrine_extensions.yaml
stof_doctrine_extensions:
    default_locale: '%app.locale%'
    orm:
        default:
            sluggable: true
            timestampable: false
            tree: true
```

### Traits

#### Repository

```bash
mkdir src/Repository/Trait
touch src/Repository/Trait/AppRepositoryTrait.php
```

```php
// Fichier src/Repository/Trait/AppRepositoryTrait.php
namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait AppRepositoryTrait
{
    /**
     * Définit la locale de la connexion à la base de données
     * 
     * @param ?string $locale Locale à utiliser (fr_FR, en_US...)
     */
    public function setLocale(?string $locale = null): void
    {
        $this->query(sprintf('SET lc_time_names=\'%s\'', is_null($locale) ? 'fr_FR' : $locale));
    }

    /**
     * Exécute une requête SQL
     * 
     * @param string $sql Script SQL à exécuter
     * @param ?array $params Paramètres du script
     * @param ?bool $fetchOne Retourner un seul enregistrement
     */
    public function query(string $sql, ?array $params = [], ?bool $fetchOne = false): array
    {
        $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql, $params);
        return $fetchOne ? $stmt->fetchAssociative() : $stmt->fetchAllAssociative();
    }

    /**
     * Retourne le QueryBuilder de sélection d'entités aléatoires
     * 
     * @param string $alias Alias de la table
     * @param ?string $criteria Critères de sélection
     * @param ?int $limit Nombre d'entité à retourner
     */
    public function findRandQb(string $alias, ?string $criteria = null, ?int $limit = 1): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder($alias)
            ->setMaxResults($limit)
            ->orderBy('RAND()');

        if (!is_null($criteria)) {
            $qb->where($criteria);
        }

        return $qb;
    }

    /**
     * Retourne une ou plusieurs entités aléatoires
     * 
     * @param string $alias Alias de la table
     * @param ?string $criteria Critères de sélection
     * @param ?int $limit Nombre d'entité à retourner
     */
    public function findRand(string $alias, ?string $criteria = null, ?int $limit = 1): array
    {
        $query = $this->findRandQb($alias, $criteria, $limit)->getQuery();
        if ($limit === 1) {
            return $query->getOneOrNullResult();
        }
        return $query->getResult();
    }

    /**
     * Sauvegarde une entité et la retourne
     * 
     * @param object $entity Entité à sauvegarder
     * @param ?bool $flush Mettre à jour la base de données
     */
    public function save(object $entity, ?bool $flush = true): object
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $entity;
    }

    /**
     * Supprime une entité
     * 
     * @param object $entity Entité à supprimer
     * @param ?bool $flush Mettre à jour la base de données
     */
    public function remove(object $entity, ?bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne la dernière entité créée
     */
    public function getLastInsert(): object|null
    {
        return $this
            ->createQueryBuilder('t')
            ->setMaxResults(1)
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
```

#### Entity

```bash
mkdir src/Entity/Trait
```

##### IdFieldTrait

```bash
touch src/Entity/Trait/IdFieldTrait.php
```

```php
// Fichier src/Entity/Trait/IdFieldTrait.php
namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait IdFieldTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
```

##### DateFieldTrait

```bash
touch src/Entity/Trait/DateFieldTrait.php
```

```php
// Fichier src/Entity/Trait/DateFieldTrait.php
namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait DateFieldTrait
{
    #[ORM\Column]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
```

##### IntervalFieldTrait

```bash
touch src/Entity/Trait/IntervalFieldTrait.php
```

```php
// Fichier src/Entity/Trait/IntervalFieldTrait.php
namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait IntervalFieldTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startAt')]
    private ?\DateTimeImmutable $endAt = null;

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;
        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;
        return $this;
    }

    public function getIntervalStart(): \DateInterval
    {
        return $this->getStartAt()->diff($this->getEndAt());
    }

    public function getIntervalEnd(): \DateInterval
    {
        return $this->getEndAt()->diff($this->getStartAt());
    }

    public function isCurrent(): bool
    {
        $now = new \DateTimeImmutable();
        return ($now > $this->getStartAt() && $now < $this->getEndAt());
    }

    public function isPast(string $field = 'endAt'): bool 
    {
        $method = sprintf('get%s', ucfirst($field));
        return $this->{$method}()->diff(new \DateTimeImmutable())->invert === 0;
    }

    public function isFuture(string $field = 'startAt'): bool 
    {
        $method = sprintf('get%s', ucfirst($field));
        return $this->{$method}()->diff(new \DateTimeImmutable())->invert === 1;
    }

    public function getPeriode(?string $interval = 'P1D'): \DatePeriod
    {
        return new \DatePeriod($this->startAt, new \DateInterval($interval), $this->endAt);
    }
}
```

### Listener

```bash
mkdir src/EventListener
touch src/EventListener/CrudListener.php
```

```php
// Fichier src/EventListener/CrudListener.php
namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\{
    PrePersistEventArgs,
    PreUpdateEventArgs
};
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::prePersist, priority: 100)]
#[AsDoctrineListener(event: Events::preUpdate, priority: 100)]
final class CrudListener
{
    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $timezone,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $now = new \DateTimeImmutable('now', new \DateTimeZone($this->timezone));

        // Date de création et modification
        if (
            $this->entityHasMethod($entity, 'setCreatedAt')  &&
            is_null($entity->getCreatedAt())
        ) {
            $entity->setCreatedAt($now);
        }

        if (
            $this->entityHasMethod($entity, 'setUpdatedAt') &&
            is_null($entity->getUpdatedAt())
        ) {
            $entity->setUpdatedAt($now);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // Date de modification
        if ($this->entityHasMethod($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone($this->timezone)));
        }
    }

    /**
     * Vérifie la présence d'une méthode dans une entité
     * 
     * @param object $entity Entité à vérifier
     * @param string $method Nom de la méthode à vérifier
     */
    private function entityHasMethod(object $entity, string $method): bool
    {
        return in_array($method, get_class_methods($entity));
    }
}
```

----

## Entité Tablette

```bash
php bin/console make:entity Tablette
```

- `name`, **string(255)** not null
- `slug`, **string(255)** not null
- `icon`, **string(50)** nullable
- `color`, **string(7)** nullable
- `description`, **text** nullable
- `lft`, **integer** not null
- `rgt`, **integer** not null
- `lvl`, **integer** not null
- `root`, **relation** Tablette ManyToOne not null
- `parent`, **relation** Tablette ManyToOne nullable

```php
// Fichier src/Entity/Tablette.php
namespace App\Entity;

use App\Entity\Trait\{DateFieldTrait, IdFieldTrait};
use App\Repository\TabletteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;

#[ORM\Entity(repositoryClass: TabletteRepository::class)]
#[Gedmo\Tree(type: 'nested')]
class Tablette
{
    use IdFieldTrait, DateFieldTrait;

    public const ICON = 'hierarchy';

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    #[Gedmo\SlugHandler(class: TreeSlugHandler::class, options: [
        'parentRelationField' => 'parent',
        'separator' => '-',
    ])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private string $color = '#000000';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    private ?int $lft = null;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    private ?int $rgt = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    private ?int $lvl = null;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Tablette::class)]
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $root;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Tablette::class, inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;

    #[ORM\OneToMany(targetEntity: Tablette::class, mappedBy: 'parent', cascade: ['persist'])]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setLft(int $lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function setLvl(int $lvl): static
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }
}
```

```bash
composer db-migration && composer db-migrate
```

### Fixtures

```php
// Fichier src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\Tablette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadTablettes($manager);
    }

    private function loadTablettes(ObjectManager $manager): self
    {
        $tablette = (new Tablette())
            ->setName('Catégories des articles')
            ->setDescription('Liste des catégories d\'articles')
            ->setIcon('category')
            ->addChild((new Tablette())->setName('Mémo'))
            ->addChild((new Tablette())->setName('Présentation'));
        $manager->persist($tablette);

        $tablette = (new Tablette())
            ->setName('Catégories des favoris')
            ->setDescription('Liste des catégories de favoris')
            ->setIcon('category')
            ->addChild((new Tablette())->setName('Site officiel'))
            ->addChild((new Tablette())->setName('Documentation'))
            ->addChild((new Tablette())->setName('API'));
        $manager->persist($tablette);

        $manager->flush();
        return $this;
    }
}
```

### TabletteController

```bash
php bin/console make:crud Tablette
```

-----

## Authentification

### Entité User

```bash
php bin/console make:user
php bin/console make:entity User
```

- `pseudo`, **string(255)** not null
- `firstname`, **string(255)** nullable
- `lastname`, **string(255)** nullable
- `phone`, **string(50)** nullable
- `color`, **string(7)** not null
- `description`, **text** nullable

### Inscription

```bash
composer require symfonycasts/verify-email-bundle symfony/ux-toggle-password
php bin/console make:registration-form
composer db-migration && composer db-migrate
```

```php
// Fichier src/Entity/User.php
namespace App\Entity;

use App\Entity\Trait\{DateFieldTrait, IdFieldTrait};
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\{PasswordAuthenticatedUserInterface, UserInterface};

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PSEUDO', fields: ['pseudo'])]
#[UniqueEntity(fields: ['pseudo'], message: 'There is already an account with this pseudo')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdFieldTrait, DateFieldTrait;

    public const ICON = 'user';
    public const ICONS = 'users';

    public const ROLES = [
        'Administrateur' => 'ROLE_ADMIN',
        'Application' => 'ROLE_APP',
        'Inactif' => 'ROLE_CLOSE',
    ];

    #[ORM\Column(type: Types::STRING, length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private ?string $pseudo = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private string $color = '#000000';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $isVerified = false;

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /** @codeCoverageIgnore */
    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getFullname(): string
    {
        return sprintf('%s %s', $this->firstname, $this->lastname);
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }
}
```

### Connexion

```bash
php bin/console make:security:form-login
```

#### Fusion Registration et Security controllers

```php
// Fichier src/Controller/SecurityController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\{ProfileForm, RegistrationFormType};
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/security', name: 'security.')]
final class SecurityController extends AppAbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly TranslatorInterface $translator
    ) {
        parent::__construct($translator);
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        MailerService $mailer,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $userRepository->save($user);
            $this->emailVerifier->sendEmailConfirmation(
                'security.verify',
                $user,
                $mailer->makeMail([
                    'html_template' => 'mails/confirmation_email.html.twig',
                    'to' => $user->getEmail(),
                    'subject' => $this->trans('Please Confirm your Email', [], 'VerifyEmailBundle'),
                ])
            );

            $this->addFlash(
                'success',
                $this->trans('An email has been sent to you to confirm your registration.', [], 'VerifyEmailBundle')
            );
            return $this->redirectToRoute('app.home');
        }

        return $this->render('security/register.html.twig', [
            'title' => __FUNCTION__,
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'verify', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('security.register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('security.register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user, $userRepository);
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('danger', $this->trans('verify_email_error', [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('security.register');
        }

        $this->addFlash('success', $this->trans('Your email address has been verified.', [], 'VerifyEmailBundle'));

        return $this->redirectToRoute('app.home');
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'title' => __FUNCTION__,
        ]);
    }

    /** @codeCoverageIgnore */
    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getAuthUser();
        $form = $this->createForm(ProfileForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user);
            $this->addFlash('toast-success', $this->trans('toast.edit'));
            return $this->redirectToRoute('security.profile');
        }
        // $this->addLog($this->trans(__FUNCTION__));
        return $this->render('security/profile.html.twig', [
            'title' => __FUNCTION__,
            'form' => $form,
        ]);
    }
}
```

-----

### AppCustomAuthenticator

```bash
php bin/console make:security:custom
```

```php
// Fichier src/Security/AppAuthenticator.php
namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response};
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\{CsrfTokenBadge, RememberMeBadge, UserBadge};
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Retourne l'url du formulaire d'authentification
     * 
     * @param Request $request
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('security.login');
    }

    public function authenticate(Request $request): Passport
    {
        $ident = $request->request->get('_username', '');
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $ident);
        return new Passport(
            new UserBadge($ident, fn(string $identifier) => $this->userRepository->getForAuthentication($identifier)),
            new PasswordCredentials($request->request->get('_password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $session = $request->getSession();
        /** @var User $user */
        $user = $token->getUser();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('toast-info', sprintf('Bonjour %s', $user->getPseudo()));
        }
        if ($targetPath = $this->getTargetPath($session, $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->urlGenerator->generate('app.home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }
        return new RedirectResponse($this->getLoginUrl($request));
    }
}
```

### Mot de passe oublié

```bash
php bin/console make:entity Token
```

- `user`, **relation** User ManyToOne not null
- `token`, **string(255)** not null

```php
// Fichier src/Entity/Token.php
namespace App\Entity;

use App\Entity\Trait\{DateFieldTrait, IdFieldTrait, IntervalFieldTrait};
use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    use DateFieldTrait, IdFieldTrait, IntervalFieldTrait;

    #[ORM\ManyToOne(inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $token = null;

    public function __toString(): string
    {
        return $this->token;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }
}
```

-----

## Logs

```bash
php bin/console make:entity Log
```

- `message`, **text** not null
- `createdAt`, **datetime_immutable** not null
- `level`, **integer** not null
- `levelName`, **string(10)** not null
- `channel`, **string(20)** not null
- `context`, **json** not null
- `extra`, **json** not null
- `user`, **relation** User ManyToOne nullable

### Processor

```php
// Fichier src/Logger/DbLogProcessor.php
namespace App\Logger;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsMonologProcessor(null, 'dbLogHandler')]
final class DbLogProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return $record;
        }

        if (!$session->isStarted()) {
            $session->start();
        }

        $request = $this->requestStack->getCurrentRequest();
        $record->extra += [
            'ip' => $request->getClientIp(),
            'browser' => $request->server->get('HTTP_USER_AGENT'),
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'ajax' => $request->isXmlHttpRequest(),
            'route' => $request->attributes->get('_route'),
            'route_params' => $request->attributes->get('_route_params'),
            'route_query' => $request->query->all(),
            'locale' => $request->getLocale(),
            'controller' => $request->attributes->get('_controller'),
            'session' => $request->getSession()->getId(),
            'user' => $this->security->getUser(),
        ];
        return $record;
    }
}
```

### Handler

```php
// Fichier src/Logger/DbLogHandler.php
namespace App\Logger;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\{Level, LogRecord};
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

#[AbstractProcessingHandler(Level::Info)]
final class DbLogHandler extends AbstractProcessingHandler
{
    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $tz,
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack
    ) {
        parent::__construct();
    }

    protected function write(LogRecord $record): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!is_null($request)) {
            $log = (new Log())
                ->setMessage($record['message'])
                ->setLevel($record['level'])
                ->setLevelName($record['level_name'])
                ->setChannel('db')
                ->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone($this->tz)));

            $extra = $record->extra;
            if (array_key_exists('user', $extra)) {
                $log->setUser($extra['user']);
                unset($extra['user']);
            }

            $this->em->persist($log->setContext($record->context)->setExtra($extra));
            $this->em->flush();
        }
    }
}
```

### CrudListener

```php
// Fichier src/EventListenr/CrudListener.php
namespace App\EventListener;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\{
    PostPersistEventArgs,
    PostRemoveEventArgs,
    PostUpdateEventArgs,
    PrePersistEventArgs,
    PreRemoveEventArgs,
    PreUpdateEventArgs
};
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::prePersist, priority: 100)]
#[AsDoctrineListener(event: Events::postPersist, priority: 100)]
#[AsDoctrineListener(event: Events::preUpdate, priority: 100)]
#[AsDoctrineListener(event: Events::postUpdate, priority: 100)]
#[AsDoctrineListener(event: Events::preRemove, priority: 100)]
#[AsDoctrineListener(event: Events::postRemove, priority: 100)]
final class CrudListener
{
    private array $removesEntities = [];

    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $timezone,
        private readonly LoggerInterface $dbLogger,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $now = new \DateTimeImmutable('now', new \DateTimeZone($this->timezone));

        // Date de création et modification
        if (
            $this->entityHasMethod($entity, 'setCreatedAt')  &&
            is_null($entity->getCreatedAt())
        ) {
            $entity->setCreatedAt($now);
        }

        if (
            $this->entityHasMethod($entity, 'setUpdatedAt') &&
            is_null($entity->getUpdatedAt())
        ) {
            $entity->setUpdatedAt($now);
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }
        $entityName = $this->getEntityShortName($entity);
        $this->dbLogger->info(sprintf('Ajout %s', $entityName), [
            'action' => 'add',
            'entity' => $entityName,
            'entity_id' => $entity->getId(),
        ]);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // Date de modification
        if ($this->entityHasMethod($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone($this->timezone)));
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }

        /** @var EntityManager $em */
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        $changes = $uow->getEntityChangeSet($entity);
        if (array_key_exists('updatedAt', $changes)) {
            unset($changes['updatedAt']);
        }
        if (!empty($changes)) {
            $entityName = $this->getEntityShortName($entity);
            $this->dbLogger->info(sprintf('Modification %s', $entityName), [
                'action' => 'update',
                'entity' => $entityName,
                'entity_id' => $entity->getId(),
                'changes' => $changes
            ]);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }
        $this->removesEntities[] = [
            'entity' => $this->getEntityShortName($entity),
            'entity_id' => $entity->getId(),
        ];
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }

        foreach ($this->removesEntities as $removed) {
            $this->dbLogger->info(sprintf('Suppression %s', $removed['entity']), [
                'action' => 'remove',
                'entity' => $removed['entity'],
                'entity_id' => $removed['entity_id'],
            ]);
        }
        $this->removesEntities = [];
    }

    /**
     * Vérifie la présence d'une méthode dans une entité
     * 
     * @param object $entity Entité à vérifier
     * @param string $method Nom de la méthode à vérifier
     */
    private function entityHasMethod(object $entity, string $method): bool
    {
        return in_array($method, get_class_methods($entity));
    }

    /**
     * Retourne le nom d'une entité
     * 
     * @param object $entité Instance d'une entité
     */
    private function getEntityShortName(object $entity): string 
    {
        $className = get_class($entity);
        $classNameParts = explode('\\', $className);
        return array_pop($classNameParts);
    }
}
```

### Configuration

```yaml
# Fichier config/packages/monolog.yaml
monolog:
    channels:
        - deprecation # Deprecations are logged in the dedicated "deprecation" channel when it exists
        - db

when@dev:
    monolog:
        handlers:
            main:
                type: stream
                path: "%kernel.logs_dir%/%kernel.environment%.log"
                level: debug
                channels: ["!event"]
            # uncomment to get logging in your browser
            # you may have to allow bigger header sizes in your Web server configuration
            #firephp:
            #    type: firephp
            #    level: info
            #chromephp:
            #    type: chromephp
            #    level: info
            console:
                type: console
                process_psr_3_messages: false
                channels: ["!event", "!doctrine", "!console"]
            db:
                channels: ['db']
                type: service
                id: monolog.handler.dbLogHandler

when@test:
    monolog:
        handlers:
            main:
                type: fingers_crossed
                action_level: error
                handler: nested
                excluded_http_codes: [404, 405]
                channels: ["!event"]
            nested:
                type: stream
                path: "%kernel.logs_dir%/%kernel.environment%.log"
                level: debug
            db:
                channels: ['db']
                type: service
                id: monolog.handler.dbLogHandler

when@prod:
    monolog:
        handlers:
            main:
                type: fingers_crossed
                action_level: error
                handler: nested
                excluded_http_codes: [404, 405]
                buffer_size: 50 # How many messages should be saved? Prevent memory leaks
            nested:
                type: stream
                path: php://stderr
                level: debug
                formatter: monolog.formatter.json
            console:
                type: console
                process_psr_3_messages: false
                channels: ["!event", "!doctrine"]
            deprecation:
                type: stream
                channels: [deprecation]
                path: php://stderr
                formatter: monolog.formatter.json
            db:
                channels: ['db']
                type: service
                id: monolog.handler.dbLogHandler
```

```yaml
# Fichier config/services.yaml
services:
    App\Logger\DbLogProcessor:
        tags:
            - { name: monolog.processor }

    monolog.handler.dbLogHandler:
        class: App\Logger\DbLogHandler
```

### Formulaire de recherche

```bash
touch src/Model/LogSearchModel.php
```

```php
// Fichier src/Model/LogSearchModel.php
namespace App\Model;

final class LogSearchModel
{
    private ?string $message = null;
    private array $users = [];
    private array $levels = [];
    private ?string $daterange = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    public function getLevels(): array
    {
        return $this->levels;
    }

    public function setLevels(array $levels): void
    {
        $this->levels = $levels;
    }

    public function getDaterange(): ?string
    {
        return $this->daterange;
    }

    public function setDaterange(?string $daterange): void
    {
        $this->daterange = $daterange;
    }
}
```

```bash
touch src/Form/LogSearchForm.php
```

```php
// Fichier src/Form/LogSearchForm.php
namespace App\Form;

use App\Entity\{Log, User};
use App\Model\LogSearchModel;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextType};
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LogSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextType::class, [
                'required' => false,
                'label' => 'Message',
                'attr' => ['placeholder' => 'Message'],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('users', EntityType::class, [
                'required' => false,
                'class' => User::class,
                // 'autocomplete' => true,
                'multiple' => true,
                'label' => false,
                'attr' => ['placeholder' => 'Users'],
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')->innerJoin('u.logs', 'l');
                }
            ])
            ->add('levels', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'choices' => array_flip(Log::LEVELS),
                'choice_label' => function ($level, $name) {
                    return sprintf('%s - %s', $level, $name);
                },
                'multiple' => true,
                // 'autocomplete' => true,
                'attr' => ['placeholder' => 'Levels']
            ])
            // ->add('daterange', TextType::class, [
            //     'required' => false,
            //     'label' => false,
            //     'attr' => [
            //         'placeholder' => 'Période',
            //         'data-controller' => 'daterange',
            //         'data-locale' => 'fr-FR',
            //         'data-time' => true,
            //     ]
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LogSearchModel::class,
            'method' => 'get',
            'csrf_protection' => false,
            // 'translation_domain' => 'EasyAdminBundle',
        ]);
    }

    /**
     * Permet de supprimer le nom du formulaire dans les inputs et paramètres de l'uri
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
```

### LogRepository

```php
// Fichier src/Repository/LogRepository.php
namespace App\Repository;

use App\Entity\Log;
use App\Model\LogSearchModel;
use App\Repository\Trait\AppRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 */
class LogRepository extends ServiceEntityRepository
{
    use AppRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function findListQb(LogSearchModel $search): QueryBuilder
    {
        $select = [
            'l.id',
            'l.createdAt',
            'l.message',
            'l.level',
            'l.channel',
            'u.pseudo',
            'JSON_EXTRACT(l.extra, \'$.route\')',
        ];

        $qb = $this
            ->createQueryBuilder('l')
            ->select(sprintf('new App\DTO\LogsDTO(%s)', join(', ', $select)))
            ->leftJoin('l.user', 'u')
            ->orderBy('l.createdAt', 'DESC');

        if ($search->getMessage()) {
            $qb
                ->andWhere('l.message LIKE :msg')
                ->setParameter('msg', '%' . $search->getMessage() . '%');
        }

        if (!empty($search->getUsers())) {
            $qb
                ->andWhere('u.id in (:userIds)')
                ->setParameter('userIds', array_map(fn ($n)=> $n->getId(), $search->getUsers()));
        }

        if (!empty($search->getLevels())) {
            $qb
                ->andWhere('l.level in (:levels)')
                ->setParameter('levels', $search->getLevels());
        }

        // if (!is_null($search->getDaterange())) {
        //     $dtr = Tools::extractDaterange($search->getDaterange());
        //     $qb
        //         ->andWhere($qb->expr()->between('l.createdAt', ':start', ':end'))
        //         ->setParameter('start', $dtr['start'])
        //         ->setParameter('end', $dtr['end']);
        // }

        return $qb;
    }
}
```

-----

## Pagination

```bash
composer require knplabs/knp-paginator-bundle
touch config/packages/knp_paginator.yaml
```

```yaml
# Fichier config/packages/knp_paginator.yaml
knp_paginator:
  page_range: 10
  default_options:
    page_name: page
    sort_field_name: sort
    sort_direction_name: direction
    distinct: true
    filter_field_name: filterField
    filter_value_name: filterValue
  template:
    pagination: '@KnpPaginator/Pagination/bootstrap_v5_pagination.html.twig'
    sortable: '@KnpPaginator/Pagination/bootstrap_v5_bi_sortable_link.html.twig'
    filtration: '@KnpPaginator/Pagination/bootstrap_v5_filtration.html.twig'
```

-----

## Pivottable

```bash
php bin/console importmap:require pivottable
touch assets/controllers/pivottable_controller.js
```

```js
// Fichier assets/controllers/pivottable_controller.js
import '../vendor/pivottable/dist/pivot.min.css'
import '../vendor/pivottable/pivottable.index.js';
// import 'pivottable/dist/pivot.fr.min.js';

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        let utils = $.pivotUtilities;
        let heatmap = utils.renderers['Heatmap'];
        let sum = utils.aggregatorTemplates.sum;
        let numberFormat = utils.numberFormat;
        let intFormat = numberFormat({ digitsAfterDecimal: 0 });
        // let moneyFormat = numberFormat({ digitsAfterDecimal: 2 });

        let location = window.location.href;
        if (this.element.dataset.path) {
            location = this.element.dataset.path;
        }

        // let elemId = this.element.id;
        let elemSelector = '#' + this.element.id;
        $.getJSON(location, function (data) {
            $(elemSelector).pivot(data.items, {
                cols: data.cols,
                rows: data.rows,
                aggregator: sum(intFormat)(data.aggregate),
                renderer: heatmap,
            });
        });
    }
}
```

-----

## UX Chart.js

```bash
composer require symfony/ux-chartjs
touch src/Service/ChartJsService.php
```

-----

### LogController

```bash
php bin/console make:crud Log
```

```php
// Fichier src/Controller/LogController.php
namespace App\Controller;

use App\Entity\Log;
use App\Form\LogSearchForm;
use App\Model\LogSearchModel;
use App\Repository\LogRepository;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/log', name: 'log.')]
final class LogController extends AppAbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(LogRepository $logRepository, Request $request): Response
    {
        $search = new LogSearchModel();
        $form = $this->createForm(LogSearchForm::class, $search);
        $form->handleRequest($request);

        $title = 'Liste logs';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
        ]);
        return $this->render('log/list.html.twig', [
            'title' => $title,
            'logs' => $this->paginate($logRepository->findListQb($search), $request),
            'search' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'show', methods: ['GET'])]
    public function show(Log $log): Response
    {
        $title = 'Voir log';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
            'entity_id' => $log->getId(),
        ]);
        return $this->render('log/show.html.twig', [
            'title' => $title,
            'log' => $log,
        ]);
    }
    
    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        $title = 'Calendrier logs';
        $this->addLog($title, [
            'action' => __FUNCTION__,
            'entity' => 'Log',
        ]);
        return $this->render('log/calendar.html.twig', [
            'title' => $title,
        ]);
    }

    #[Route('/pivottable', name: 'pivottable')]
    public function pivottable(LogRepository $logRepository, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('log.list');
        }
        $this->addLog(ucfirst(__FUNCTION__), [
            'action' => __FUNCTION__,
            'entity' => 'Log'
        ]);
        $data = [
            'rows' => ['Annee', 'Mois'],
            'cols' => ['Jour'],
            'aggregate' => ['cnt'],
            'items' => $logRepository->countByAllTime(),
        ];
        return $this->json($data);
    }
}
```

-----

## EasyAdmin

```bash
composer require easycorp/easyadmin-bundle
php bin/console make:admin:dashboard
```

### DashboardController

```php
// Fichier src/Controller/Admin/DashboardController.php
namespace App\Controller\Admin;

use App\Entity\{Log, Tablette, Token, User};
use App\Utils\Images;
use Doctrine\DBAL\Connection;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Dashboard, MenuItem, UserMenu};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly Connection $db) {}

    public function index(): Response
    {
        $serverInfos = $this->db->executeQuery("SELECT
                (select @@GLOBAL.hostname) as serverName
                , (select @@GLOBAL.version) as versionText
                , (select @@GLOBAL.lc_time_names) as language
                , (select @@GLOBAL.character_set_server) as charset
                , (select @@GLOBAL.collation_server) as collation
                , (select @@GLOBAL.default_storage_engine) as engine
                , (select @@GLOBAL.datadir) as dbFileDir;")
            ->fetchAssociative();
        $dbName = $this->db->getDatabase();
        $dbCreated = $this->db->executeQuery("SELECT min(t.CREATE_TIME) as dbCreatedAt
                FROM information_schema.tables t 
                WHERE t.TABLE_SCHEMA = :dbname;", ['dbname' => $dbName])
            ->fetchOne();

        $dbSize = $this->db->executeQuery("SELECT sum(t.data_length + t.index_length) as dbsize
                    FROM information_schema.tables t
                    WHERE t.table_schema = :dbname;", ['dbname' => $dbName])
            ->fetchOne();

        return $this->render('admin/index.html.twig', [
            'title' => 'dashboard',
            'php_os' => PHP_OS_FAMILY,
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'php_path' => PHP_BINDIR,
            'php_ext_path' => PHP_EXTENSION_DIR,
            'memory_peak' => memory_get_peak_usage(true),
            'db_server_info' => $serverInfos,
            'db_name' => $dbName,
            'db_created' => $dbCreated,
            'db_size' => $dbSize,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        $logoFile = $this->getParameter('app.logo');
        $appName = $this->getParameter('app.name');
        $title = sprintf(
            '<img src="%s" alt="Logo %s" class="me-1">Administration %s',
            Images::make($logoFile)->resize(30, 30)->encode('data-url')->getEncoded(),
            $appName,
            $appName,
        );
        return Dashboard::new()
            ->setTitle($title)
            ->setFaviconPath('images/logo.png')
            ->setTextDirection('ltr')
            ->setLocales(explode('|', $this->getParameter('app.locales')))
            ->setTranslationDomain('EasyAdminBundle')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::subMenu('Application', 'fas fa-gear text-info')->setSubItems([
            MenuItem::linkToCrud('Tablettes', 'fas fa-sitemap', Tablette::class),
            MenuItem::linkToCrud('Logs', 'fas fa-calendar-days', Log::class),
            MenuItem::linkToCrud('Users', 'fas fa-users', User::class),
            MenuItem::linkToCrud('Tokens', 'fas fa-key', Token::class),
        ]);

        yield MenuItem::section('Links', 'fas fa-link text-info');
        yield MenuItem::linkToRoute('Application', 'fab fa-symfony', 'app.home');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $userMenu = parent::configureUserMenu($user)
            ->displayUserName(true)
            ->setName($user->getPseudo())
            ->displayUserAvatar(true)
            ->setGravatarEmail($user->getEmail());

        return $userMenu;
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPageTitle('index', 'Liste %entity_label_plural%')
            ->setDateIntervalFormat('%%y Année(s) %%m Mois %%d Jour(s)')
            ->setTimezone($this->getParameter('app.timezone'))
            ->setNumberFormat('%.2d')
            ->setPaginatorPageSize(15)
            ->setAutofocusSearch()
            ->showEntityActionsInlined();
    }
}
```

### AppAbstractCrudController

```php
// Fichier src/Controller/Admin/AppAbstractCrudController.php
namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AppAbstractCrudController extends AbstractCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions

            // Page INDEX
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fas fa-eye text-info')
                    ->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit text-primary')
                    ->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash text-danger')
                    ->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fas fa-plus-circle')
                    ->setCssClass('btn btn-success');
            })

            // Page EDIT
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->update(Crud::PAGE_EDIT, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fas fa-eye')
                    ->setCssClass('btn btn-info');
            })
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(Crud::PAGE_EDIT, Action::INDEX, function (Action $action) {
                return $action
                    ->setIcon('fas fa-list')
                    ->setCssClass('btn btn-muted');
            })

            // Page DETAIL
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setIcon('fas fa-list');
            })

            // Page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action
                    ->setIcon('fas fa-check')
                    ->setCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action
                    ->setIcon('fas fa-redo');
            })
        ;
    }
}
```

### Crud Controllers

```bash
php bin/console make:admin:crud
```

-----

## PDF

```bash
composer require setasign/fpdf
touch src/Pdf/MyFPDF.php
```

### Configuration

```yaml
# Fichier config/services.yaml
parameters:
    app.pdf:
        orientation: P
        unit: mm
        size: A4
        font: helvetica
        font_size: 10
        format: A4
        tmp_dir: '%app.tmp_dir%/pdf'
        text_color: '#000000'
        draw_color: '#000000'
        fill_color: '#ecf0f1'
        php: true
        creator: '%app.name%'
        logo: '%app.logo%'
```

### MyFPDF

```php
// Fichier src/Pdf/MyFPDF.php
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

        'footer_height' => false, // Hauteur du pied de page. false pour aucun.
        'footer_border' => 0, // Bordure(s) du pied de page (0, 1, L, T, R ou B)
        'footer_fill' => false, // Remplissage du pied de page

        'pagination_enabled' => false, // Active la pagination (false, header ou footer)
        'pagination_border' => 0, // Bordure de la pagination (0, 1, L, T, R ou B)
        'pagination_fill' => false, // Code hexa ou booléen. Si true, c'est la couleur de remplissage par défaut.
        'pagination_align' => 'R', // Alignement de la pagination (L, C ou R).

        'timezone' => 'Europe/Paris',
        'tmp_dir' => null,
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

        // Couleurs
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
     * En-tête
     * Appelée automatiquement par AddPage().
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
            )
            ->setCursor($this->lMargin, $this->tMargin);

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
            size: ($size == 0) ? $this->options->font_size : $size
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
     * Convertit de l'UTF-8 en Windows-1252 si nécessaire
     * @param string $text Texte à convertir
     */
    public function convertText(?string $text = null): string
    {
        if (is_null($text)) {
            return '';
        }
        if (mb_detect_encoding($text) === 'UTF-8') {
            $text = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        }
        return $text;
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

        $nbFiles = in_array('joinedFiles', $vars)
            ? count($this->{'joinedFiles'})
            : 0;

        $nbBookmarks = in_array('bookmarks', $vars)
            ? count($this->{'bookmarks'})
            : 0;

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
            'Fichiers' => $nbFiles,
            'Signets' => $nbBookmarks,
            'Javascript' => in_array('javascript', $vars) ? 'Oui' : 'Non',
            'Version PHP' => PHP_VERSION,
            'Version FPDF' => $this->metadata['Producer'],
            'Classe PDF' => get_class($this),
            'Parents' => join(', ', array_map(function (string $name) {
                return Tools::namespaceSplit($name, true);
            }, class_parents($this))),
            'Traits' => join(', ', array_map(function (string $name) {
                return Tools::namespaceSplit($name, true);
            }, class_uses($this))),
            'Fichier classe' => __FILE__,
        ];

        return new Collection($infos, 'Informations du document PDF');
    }

    /**
     * Imprime la liste des informations du document
     * 
     * @param ?bool $addPage Si vrai, une page est ajoutée pour les informations
     */
    public function printInfos(?bool $addPage = true): self
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
            $width = $this->GetStringWidth($this->convertText($key)) + $this->getMargins()->cell;
            if ($maxWidth < $width) {
                $maxWidth = $width;
            }
        }

        // Titre
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
}
```

### AppPdf

```bash
touch src/Pdf/AppPdf.php
```

```php
// Fichier src/Pdf/AppPdf.php
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
```

### PdfService

```bash
touch src/Services/PdfService.php
```

```php
// Fichier src/Services/PdfService.php
namespace App\Service;

use App\Pdf\AppPdf;
use App\Utils\Images;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PdfService
{
    public function __construct(
        #[Autowire('%app.pdf%')]
        private readonly array $config,
        #[Autowire('%app.docs_dir%')]
        private readonly string $docDir,
    ) {}

    /**
     * Retourne un document PDF de l'application
     * 
     * @param ?array $options Options du document
     * @param ?array $data Données du document
     */
    public function make(?array $options = [], ?array $data = []): AppPdf
    {
        return new AppPdf(
            array_merge($this->getDefaultOptions(), $options),
            array_merge($this->getDefaultData(), $data)
        );
    }

    /**
     * Retourne les options par défaut d'un document PDF MCV
     */
    private function getDefaultOptions(): array
    {
        $logoFilename = sprintf('%s/logo-pdf.png', dirname($this->config['logo']));
        if (!file_exists($logoFilename)) {
            Images::make($this->config['logo'])
                ->resize(50, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save($logoFilename, 90, 'PNG');
        }
        return [
            'orientation' => $this->config['orientation'],
            'unit' => $this->config['unit'],
            'size' => $this->config['size'],
            'font_family' => $this->config['font'],
            'font_size' => $this->config['font_size'],
            'tmp_dir' => $this->config['tmp_dir'],
            'creator' => $this->config['creator'],
            'keywords' => $this->config['creator'],
            'logo' => $logoFilename,
            'logo_link' => 'https://github.com/rcnchris/sf64-base',
            'text_color' => $this->config['text_color'],
            'draw_color' => $this->config['draw_color'],
            'fill_color' => $this->config['fill_color'],

            'header_height' => 15,
            'header_fill' => false,
            'header_border' => 'B',

            'footer_height' => 15,
            'footer_fill' => false,
            'footer_border' => 'T',

            'pagination_enabled' => 'footer',

            'table_align' => 'C',
            'table_header_color' => '#7f8c8d',
            'table_header_text_color' => '#FFFFFF',
            'table_row_color1' => '#bdc3c7',
            'table_row_color2' => '#ecf0f1',
        ];
    }

    /**
     * Retourne les données par défaut d'un document PDF MCV
     */
    private function getDefaultData(): array
    {
        return [];
    }
}
```