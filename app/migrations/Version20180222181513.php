<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180222181513 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_account (account_id INT AUTO_INCREMENT NOT NULL, balance DOUBLE PRECISION NOT NULL, PRIMARY KEY(account_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('  CREATE TABLE pa_account_ledgers (ledger_id INT AUTO_INCREMENT NOT NULL, account INT DEFAULT NULL, balance DOUBLE PRECISION NOT NULL, debit DOUBLE PRECISION DEFAULT NULL, credit DOUBLE PRECISION DEFAULT NULL, created DATETIME DEFAULT NULL, description VARCHAR(500) NOT NULL, cas VARCHAR(200) DEFAULT NULL, INDEX IDX_DB6234B17D3656A4 (account), PRIMARY KEY(ledger_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_account_ledgers ADD CONSTRAINT FK_DB6234B17D3656A4 FOREIGN KEY (account) REFERENCES pa_account (account_id);');
        $this->addSql(' ALTER TABLE pa_travel_agency ADD account INT DEFAULT NULL;');
        $this->addSql(' ALTER TABLE pa_travel_agency ADD CONSTRAINT FK_E49923767D3656A4 FOREIGN KEY (account) REFERENCES pa_account (account_id);');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E49923767D3656A4 ON pa_travel_agency (account);');









    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
