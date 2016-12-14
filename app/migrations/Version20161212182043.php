<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161212182043 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE accommodation_modality_frequency (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, modality INT DEFAULT NULL, startDate DATE NOT NULL, endDate DATE NOT NULL, INDEX IDX_4C8CC6672D385412 (accommodation), INDEX IDX_4C8CC667307988C0 (modality), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accommodation_modality_frequency ADD CONSTRAINT FK_4C8CC6672D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE accommodation_modality_frequency ADD CONSTRAINT FK_4C8CC667307988C0 FOREIGN KEY (modality) REFERENCES nomenclator (nom_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE accommodation_modality_frequency');

    }
}
