<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150720224726 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        if (!$this->connection->getSchemaManager()->tablesExist(array('ownershipreservationstat'))) {
            $this->addSql('CREATE TABLE ownershipreservationstat (stat_id INT AUTO_INCREMENT NOT NULL, stat_accommodation INT DEFAULT NULL, stat_nomenclator INT DEFAULT NULL, stat_date DATE NOT NULL, stat_value VARCHAR(255) NOT NULL, INDEX IDX_E35D06D71B93A1F9 (stat_accommodation), INDEX IDX_E35D06D7210B833C (stat_nomenclator), PRIMARY KEY(stat_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
            $this->addSql('ALTER TABLE ownershipreservationstat ADD CONSTRAINT FK_E35D06D71B93A1F9 FOREIGN KEY (stat_accommodation) REFERENCES ownership (own_id)');
            $this->addSql('ALTER TABLE ownershipreservationstat ADD CONSTRAINT FK_E35D06D7210B833C FOREIGN KEY (stat_nomenclator) REFERENCES nomenclatorstat (nom_id)');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownershipreservationstat');
    }
}
