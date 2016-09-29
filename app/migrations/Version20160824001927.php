<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160824001927 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_agency_package (travel_agency INT NOT NULL, package INT NOT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, datePayment DATETIME DEFAULT NULL, payedAmount NUMERIC(2, 0) DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_99B08374526A7CD8 (travel_agency), INDEX IDX_99B08374DE686795 (package), INDEX IDX_99B083747B982B81 (id_created_by), INDEX IDX_99B083743DEB85F5 (id_modified_by), PRIMARY KEY(travel_agency, package)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pa_client (id INT AUTO_INCREMENT NOT NULL, country INT DEFAULT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_A9F04BF65373C966 (country), INDEX IDX_A9F04BF67B982B81 (id_created_by), INDEX IDX_A9F04BF63DEB85F5 (id_modified_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pa_tour_operator (travel_agency INT NOT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, tourOperator INT NOT NULL, INDEX IDX_FBAAA11C526A7CD8 (travel_agency), INDEX IDX_FBAAA11C1A72A12B (tourOperator), INDEX IDX_FBAAA11C7B982B81 (id_created_by), INDEX IDX_FBAAA11C3DEB85F5 (id_modified_by), PRIMARY KEY(travel_agency, tourOperator)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_agency_package ADD CONSTRAINT FK_99B08374526A7CD8 FOREIGN KEY (travel_agency) REFERENCES pa_travel_agency (id)');
        $this->addSql('ALTER TABLE pa_agency_package ADD CONSTRAINT FK_99B08374DE686795 FOREIGN KEY (package) REFERENCES pa_package (id)');
        $this->addSql('ALTER TABLE pa_agency_package ADD CONSTRAINT FK_99B083747B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_agency_package ADD CONSTRAINT FK_99B083743DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_client ADD CONSTRAINT FK_A9F04BF65373C966 FOREIGN KEY (country) REFERENCES country (co_id)');
        $this->addSql('ALTER TABLE pa_client ADD CONSTRAINT FK_A9F04BF67B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_client ADD CONSTRAINT FK_A9F04BF63DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_tour_operator ADD CONSTRAINT FK_FBAAA11C526A7CD8 FOREIGN KEY (travel_agency) REFERENCES pa_travel_agency (id)');
        $this->addSql('ALTER TABLE pa_tour_operator ADD CONSTRAINT FK_FBAAA11C1A72A12B FOREIGN KEY (tourOperator) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_tour_operator ADD CONSTRAINT FK_FBAAA11C7B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_tour_operator ADD CONSTRAINT FK_FBAAA11C3DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_agency_package');
        $this->addSql('DROP TABLE pa_client');
        $this->addSql('DROP TABLE pa_tour_operator');

    }
}
