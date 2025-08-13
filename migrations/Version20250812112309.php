<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812112309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création table user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (
          id INT AUTO_INCREMENT NOT NULL,
          email VARCHAR(180) NOT NULL,
          roles JSON NOT NULL,
          password VARCHAR(255) NOT NULL,
          pseudo VARCHAR(20) NOT NULL,
          firstname VARCHAR(50) DEFAULT NULL,
          lastname VARCHAR(50) DEFAULT NULL,
          phone VARCHAR(20) DEFAULT NULL,
          color VARCHAR(7) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          is_verified TINYINT(1) NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
    }
}
