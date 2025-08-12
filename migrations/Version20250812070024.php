<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812070024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tablette (
          id INT AUTO_INCREMENT NOT NULL,
          tree_root INT DEFAULT NULL,
          parent_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          icon VARCHAR(50) DEFAULT NULL,
          color VARCHAR(7) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          lft INT NOT NULL,
          rgt INT NOT NULL,
          lvl INT NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_508CDDD7989D9B62 (slug),
          INDEX IDX_508CDDD7A977936C (tree_root),
          INDEX IDX_508CDDD7727ACA70 (parent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (
          id BIGINT AUTO_INCREMENT NOT NULL,
          body LONGTEXT NOT NULL,
          headers LONGTEXT NOT NULL,
          queue_name VARCHAR(190) NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_75EA56E0FB7336F0 (queue_name),
          INDEX IDX_75EA56E0E3BD61CE (available_at),
          INDEX IDX_75EA56E016BA31DB (delivered_at),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          tablette
        ADD
          CONSTRAINT FK_508CDDD7A977936C FOREIGN KEY (tree_root) REFERENCES tablette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          tablette
        ADD
          CONSTRAINT FK_508CDDD7727ACA70 FOREIGN KEY (parent_id) REFERENCES tablette (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tablette DROP FOREIGN KEY FK_508CDDD7A977936C');
        $this->addSql('ALTER TABLE tablette DROP FOREIGN KEY FK_508CDDD7727ACA70');
        $this->addSql('DROP TABLE tablette');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
