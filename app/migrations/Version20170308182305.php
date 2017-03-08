<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170308182305 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE effective_method_payment (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, contact_name VARCHAR(255) NOT NULL, identityNumber VARCHAR(255) NOT NULL, INDEX IDX_4BECF7B32D385412 (accommodation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer_method_payment (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, titular VARCHAR(255) NOT NULL, accountNumber VARCHAR(255) NOT NULL, bankBranchName VARCHAR(255) NOT NULL, identityNumber VARCHAR(255) NOT NULL, accountType INT DEFAULT NULL, INDEX IDX_F4EA43A32D385412 (accommodation), INDEX IDX_F4EA43A3217CB8B3 (accountType), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE effective_method_payment ADD CONSTRAINT FK_4BECF7B32D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE transfer_method_payment ADD CONSTRAINT FK_F4EA43A32D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE transfer_method_payment ADD CONSTRAINT FK_F4EA43A3217CB8B3 FOREIGN KEY (accountType) REFERENCES nomenclator (nom_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE effective_method_payment');
        $this->addSql('DROP TABLE transfer_method_payment');
    }
}
