<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710172641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, collecte_id INT DEFAULT NULL, order_invoice_id INT DEFAULT NULL, shop_id INT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_90651744710A9AC6 (collecte_id), UNIQUE INDEX UNIQ_906517449A530CA8 (order_invoice_id), INDEX IDX_906517444D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744710A9AC6 FOREIGN KEY (collecte_id) REFERENCES collecte (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449A530CA8 FOREIGN KEY (order_invoice_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517444D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744710A9AC6');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449A530CA8');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517444D16C4DD');
        $this->addSql('DROP TABLE invoice');
    }
}
