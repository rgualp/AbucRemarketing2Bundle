<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160824211206 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_reservation (id INT AUTO_INCREMENT NOT NULL, client INT DEFAULT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, number INT NOT NULL, arrivalHour TIME NOT NULL, adults INT NOT NULL, infants INT NOT NULL, children INT NOT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_8F11F49DC7440455 (client), INDEX IDX_8F11F49D7B982B81 (id_created_by), INDEX IDX_8F11F49D3DEB85F5 (id_modified_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pa_reservation_detail (reservation INT NOT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, reservationDetail INT NOT NULL, INDEX IDX_3F93CCBC42C84955 (reservation), INDEX IDX_3F93CCBCE0ED469E (reservationDetail), INDEX IDX_3F93CCBC7B982B81 (id_created_by), INDEX IDX_3F93CCBC3DEB85F5 (id_modified_by), PRIMARY KEY(reservation, reservationDetail)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_reservation ADD CONSTRAINT FK_8F11F49DC7440455 FOREIGN KEY (client) REFERENCES pa_client (id)');
        $this->addSql('ALTER TABLE pa_reservation ADD CONSTRAINT FK_8F11F49D7B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_reservation ADD CONSTRAINT FK_8F11F49D3DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBC42C84955 FOREIGN KEY (reservation) REFERENCES pa_reservation (id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBCE0ED469E FOREIGN KEY (reservationDetail) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBC7B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBC3DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('DROP TABLE pa_client_reservation');

        $this->addSql('ALTER TABLE pa_client ADD passport VARCHAR(255) DEFAULT NULL, ADD travelAgency INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pa_client ADD CONSTRAINT FK_A9F04BF655F8A8FA FOREIGN KEY (travelAgency) REFERENCES pa_travel_agency (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_reservation_detail DROP FOREIGN KEY FK_3F93CCBC42C84955');
        $this->addSql('CREATE TABLE pa_client_reservation (client INT NOT NULL, reservation INT NOT NULL, id_modified_by INT DEFAULT NULL, id_created_by INT DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, INDEX IDX_1AE796DAC7440455 (client), INDEX IDX_1AE796DA42C84955 (reservation), INDEX IDX_1AE796DA7B982B81 (id_created_by), INDEX IDX_1AE796DA3DEB85F5 (id_modified_by), PRIMARY KEY(client, reservation)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DA3DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DA42C84955 FOREIGN KEY (reservation) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DA7B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_client_reservation ADD CONSTRAINT FK_1AE796DAC7440455 FOREIGN KEY (client) REFERENCES pa_client (id)');
        $this->addSql('DROP TABLE pa_reservation');
        $this->addSql('DROP TABLE pa_reservation_detail');
        $this->addSql('ALTER TABLE pa_client DROP FOREIGN KEY FK_A9F04BF655F8A8FA');
        $this->addSql('DROP INDEX IDX_A9F04BF655F8A8FA ON pa_client');
        $this->addSql('ALTER TABLE pa_client DROP passport, DROP travelAgency');

    }
}
