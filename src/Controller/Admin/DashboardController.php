<?php

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
