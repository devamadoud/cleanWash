<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503212615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D273BE94C');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D273BE94C FOREIGN KEY (collected_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer CHANGE full_name full_name VARCHAR(255) DEFAULT NULL, CHANGE adress adress VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D273BE94C');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D273BE94C FOREIGN KEY (collected_by_id) REFERENCES employe (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE customer CHANGE full_name full_name VARCHAR(255) NOT NULL, CHANGE adress adress VARCHAR(255) NOT NULL');
    }
}
