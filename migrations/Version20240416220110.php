<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416220110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clothing_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collecte (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, shop_id INT NOT NULL, collected_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, INDEX IDX_55AE4A3D9395C3F3 (customer_id), INDEX IDX_55AE4A3D4D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collecte_detailles (id INT AUTO_INCREMENT NOT NULL, collecte_id INT NOT NULL, clothing_type_id INT NOT NULL, quantity INT NOT NULL, unit_price DOUBLE PRECISION NOT NULL, INDEX IDX_849C801B710A9AC6 (collecte_id), INDEX IDX_849C801B7D306B88 (clothing_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, collecte_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, paid_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', paiment_mode VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6D28840D710A9AC6 (collecte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D4D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('ALTER TABLE collecte_detailles ADD CONSTRAINT FK_849C801B710A9AC6 FOREIGN KEY (collecte_id) REFERENCES collecte (id)');
        $this->addSql('ALTER TABLE collecte_detailles ADD CONSTRAINT FK_849C801B7D306B88 FOREIGN KEY (clothing_type_id) REFERENCES clothing_type (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D710A9AC6 FOREIGN KEY (collecte_id) REFERENCES collecte (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D9395C3F3');
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D4D16C4DD');
        $this->addSql('ALTER TABLE collecte_detailles DROP FOREIGN KEY FK_849C801B710A9AC6');
        $this->addSql('ALTER TABLE collecte_detailles DROP FOREIGN KEY FK_849C801B7D306B88');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D710A9AC6');
        $this->addSql('DROP TABLE clothing_type');
        $this->addSql('DROP TABLE collecte');
        $this->addSql('DROP TABLE collecte_detailles');
        $this->addSql('DROP TABLE payment');
    }
}
