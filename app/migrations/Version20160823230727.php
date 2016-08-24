<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160823230727 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_contact (id INT AUTO_INCREMENT NOT NULL, travel_agency INT DEFAULT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, mobile VARCHAR(255) DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_3D3A025526A7CD8 (travel_agency), INDEX IDX_3D3A0257B982B81 (id_created_by), INDEX IDX_3D3A0253DEB85F5 (id_modified_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pa_package (id INT AUTO_INCREMENT NOT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(2, 0) NOT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_91D921887B982B81 (id_created_by), INDEX IDX_91D921883DEB85F5 (id_modified_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pa_travel_agency (id INT AUTO_INCREMENT NOT NULL, country INT DEFAULT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, address VARCHAR(500) NOT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_E49923765373C966 (country), INDEX IDX_E49923767B982B81 (id_created_by), INDEX IDX_E49923763DEB85F5 (id_modified_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_contact ADD CONSTRAINT FK_3D3A025526A7CD8 FOREIGN KEY (travel_agency) REFERENCES pa_travel_agency (id)');
        $this->addSql('ALTER TABLE pa_contact ADD CONSTRAINT FK_3D3A0257B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_contact ADD CONSTRAINT FK_3D3A0253DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_package ADD CONSTRAINT FK_91D921887B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_package ADD CONSTRAINT FK_91D921883DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_travel_agency ADD CONSTRAINT FK_E49923765373C966 FOREIGN KEY (country) REFERENCES country (co_id)');
        $this->addSql('ALTER TABLE pa_travel_agency ADD CONSTRAINT FK_E49923767B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_travel_agency ADD CONSTRAINT FK_E49923763DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_contact DROP FOREIGN KEY FK_3D3A025526A7CD8');
        $this->addSql('DROP TABLE pa_contact');
        $this->addSql('DROP TABLE pa_package');
        $this->addSql('DROP TABLE pa_travel_agency');
    }
}
