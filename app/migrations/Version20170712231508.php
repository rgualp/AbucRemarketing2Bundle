<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170712231508 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pending_payment DROP FOREIGN KEY FK_A647739C5E411D4');
        $this->addSql('DROP INDEX IDX_A647739C5E411D4 ON pending_payment');
        $this->addSql('ALTER TABLE pending_payment ADD reservation INT DEFAULT NULL, ADD booking INT DEFAULT NULL, DROP user_casa');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739C42C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739CE00CEDDE FOREIGN KEY (booking) REFERENCES booking (booking_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE pending_payment DROP FOREIGN KEY FK_A647739C42C84955');
        $this->addSql('ALTER TABLE pending_payment DROP FOREIGN KEY FK_A647739CE00CEDDE');
        $this->addSql('DROP INDEX IDX_A647739C42C84955 ON pending_payment');
        $this->addSql('DROP INDEX IDX_A647739CE00CEDDE ON pending_payment');
        $this->addSql('ALTER TABLE pending_payment ADD user_casa INT NOT NULL, DROP reservation, DROP booking');
        $this->addSql('ALTER TABLE pending_payment ADD CONSTRAINT FK_A647739C5E411D4 FOREIGN KEY (user_casa) REFERENCES ownership (own_id)');
        $this->addSql('CREATE INDEX IDX_A647739C5E411D4 ON pending_payment (user_casa)');

    }
}
