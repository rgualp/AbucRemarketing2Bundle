<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170120124809 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Create table
        $this->addSql('CREATE TABLE cpayment_ownreservation (cancel INT NOT NULL, ownreservation INT NOT NULL, INDEX IDX_B6BD307FEBA5B1K9 (cancel), INDEX IDX_B6BD307FEBA5B1DE (ownreservation), PRIMARY KEY(cancel, ownreservation)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        //Foreign key
        $this->addSql('ALTER TABLE cpayment_ownreservation ADD CONSTRAINT FK_B6BD307FEBA5B1K9 FOREIGN KEY (cancel) REFERENCES cancel_payment (cancel_id)');
        $this->addSql('ALTER TABLE cpayment_ownreservation ADD CONSTRAINT FK_B6BD307FEBA5B1DE FOREIGN KEY (ownreservation) REFERENCES ownershipreservation (own_res_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
