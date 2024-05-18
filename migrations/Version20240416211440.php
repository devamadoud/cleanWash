<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416211440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, full_name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, adress VARCHAR(255) NOT NULL, INDEX IDX_81398E094D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, com_name VARCHAR(255) NOT NULL, adress VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_AC6A4CA27E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E094D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA27E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E094D16C4DD');
        $this->addSql('ALTER TABLE shop DROP FOREIGN KEY FK_AC6A4CA27E3C61F9');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE shop');
    }
}
