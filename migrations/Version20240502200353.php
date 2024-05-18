<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240502200353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte ADD collected_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D273BE94C FOREIGN KEY (collected_by_id) REFERENCES employe (id)');
        $this->addSql('CREATE INDEX IDX_55AE4A3D273BE94C ON collecte (collected_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D273BE94C');
        $this->addSql('DROP INDEX IDX_55AE4A3D273BE94C ON collecte');
        $this->addSql('ALTER TABLE collecte DROP collected_by_id');
    }
}
