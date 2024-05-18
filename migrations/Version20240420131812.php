<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420131812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE employe (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, shop_id INT DEFAULT NULL, poste VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F804D3B9A76ED395 (user_id), INDEX IDX_F804D3B94D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B94D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B9A76ED395');
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B94D16C4DD');
        $this->addSql('DROP TABLE employe');
    }
}
