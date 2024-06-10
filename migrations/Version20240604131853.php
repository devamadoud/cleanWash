<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240604131853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte ADD payment_id INT DEFAULT NULL, DROP payed');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55AE4A3D4C3A3BB ON collecte (payment_id)');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D710A9AC6');
        $this->addSql('DROP INDEX UNIQ_6D28840D710A9AC6 ON payment');
        $this->addSql('ALTER TABLE payment DROP collecte_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D4C3A3BB');
        $this->addSql('DROP INDEX UNIQ_55AE4A3D4C3A3BB ON collecte');
        $this->addSql('ALTER TABLE collecte ADD payed TINYINT(1) DEFAULT NULL, DROP payment_id');
        $this->addSql('ALTER TABLE payment ADD collecte_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D710A9AC6 FOREIGN KEY (collecte_id) REFERENCES collecte (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D710A9AC6 ON payment (collecte_id)');
    }
}
