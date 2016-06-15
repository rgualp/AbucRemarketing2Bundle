<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160615202013 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ownershippayment (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, service INT DEFAULT NULL, method INT DEFAULT NULL, payed_amount NUMERIC(10, 0) NOT NULL, payment_date DATETIME NOT NULL, creation_dat DATETIME NOT NULL, INDEX IDX_538255BB2D385412 (accommodation), INDEX IDX_538255BBE19D9AD2 (service), INDEX IDX_538255BB5E593A60 (method), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ownershippayment ADD CONSTRAINT FK_538255BB2D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE ownershippayment ADD CONSTRAINT FK_538255BBE19D9AD2 FOREIGN KEY (service) REFERENCES mycpservice (id)');
        $this->addSql('ALTER TABLE ownershippayment ADD CONSTRAINT FK_538255BB5E593A60 FOREIGN KEY (method) REFERENCES nomenclator (nom_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownershippayment');
    }
}
