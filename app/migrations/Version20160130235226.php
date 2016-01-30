<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160130235226 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE old_payment CHANGE creation_date creation_date VARCHAR(255) DEFAULT NULL, CHANGE arrival_date arrival_date VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE old_reservation CHANGE creation_date creation_date VARCHAR(255) DEFAULT NULL, CHANGE arrival_date arrival_date VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE old_payment CHANGE creation_date creation_date DATE DEFAULT NULL, CHANGE arrival_date arrival_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE old_reservation CHANGE creation_date creation_date DATE DEFAULT NULL, CHANGE arrival_date arrival_date DATE DEFAULT NULL');

    }
}
