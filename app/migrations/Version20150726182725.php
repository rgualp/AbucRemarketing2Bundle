<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150726182725 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownershipreservationstat DROP FOREIGN KEY FK_E35D06D71B93A1F9');
        $this->addSql('ALTER TABLE ownershipreservationstat DROP FOREIGN KEY FK_E35D06D7210B833C');
        $this->addSql('ALTER TABLE ownershipreservationstat ADD stat_date_to DATE NOT NULL, CHANGE stat_date stat_date_from DATE NOT NULL');
        $this->addSql('DROP INDEX idx_e35d06d71b93a1f9 ON ownershipreservationstat');
        $this->addSql('CREATE INDEX IDX_836A28BA1B93A1F9 ON ownershipreservationstat (stat_accommodation)');
        $this->addSql('DROP INDEX idx_e35d06d7210b833c ON ownershipreservationstat');
        $this->addSql('CREATE INDEX IDX_836A28BA210B833C ON ownershipreservationstat (stat_nomenclator)');
        $this->addSql('ALTER TABLE ownershipreservationstat ADD CONSTRAINT FK_E35D06D71B93A1F9 FOREIGN KEY (stat_accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE ownershipreservationstat ADD CONSTRAINT FK_E35D06D7210B833C FOREIGN KEY (stat_nomenclator) REFERENCES nomenclatorstat (nom_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownershipreservationstat DROP FOREIGN KEY FK_836A28BA1B93A1F9');
        $this->addSql('ALTER TABLE ownershipreservationstat DROP FOREIGN KEY FK_836A28BA210B833C');
        $this->addSql('ALTER TABLE ownershipreservationstat ADD stat_date DATE NOT NULL, DROP stat_date_from, DROP stat_date_to');
        $this->addSql('DROP INDEX idx_836a28ba1b93a1f9 ON ownershipreservationstat');
        $this->addSql('CREATE INDEX IDX_E35D06D71B93A1F9 ON ownershipreservationstat (stat_accommodation)');
        $this->addSql('DROP INDEX idx_836a28ba210b833c ON ownershipreservationstat');
        $this->addSql('CREATE INDEX IDX_E35D06D7210B833C ON ownershipreservationstat (stat_nomenclator)');
        $this->addSql('ALTER TABLE ownershipreservationstat ADD CONSTRAINT FK_836A28BA1B93A1F9 FOREIGN KEY (stat_accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE ownershipreservationstat ADD CONSTRAINT FK_836A28BA210B833C FOREIGN KEY (stat_nomenclator) REFERENCES nomenclatorstat (nom_id)');
    }
}
