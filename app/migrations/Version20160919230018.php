<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160919230018 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_reservation_detail');
        $this->addSql('CREATE TABLE pa_reservation_detail (id INT AUTO_INCREMENT NOT NULL, reservation INT DEFAULT NULL, id_created_by INT DEFAULT NULL, id_modified_by INT DEFAULT NULL, created DATETIME DEFAULT NULL, modified DATETIME DEFAULT NULL, reservationDetail INT DEFAULT NULL, openReservationDetail INT DEFAULT NULL, INDEX IDX_3F93CCBC42C84955 (reservation), INDEX IDX_3F93CCBCE0ED469E (reservationDetail), INDEX IDX_3F93CCBCDBB3132 (openReservationDetail), INDEX IDX_3F93CCBC7B982B81 (id_created_by), INDEX IDX_3F93CCBC3DEB85F5 (id_modified_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBC42C84955 FOREIGN KEY (reservation) REFERENCES pa_reservation (id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBCE0ED469E FOREIGN KEY (reservationDetail) REFERENCES generalreservation (gen_res_id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBCDBB3132 FOREIGN KEY (openReservationDetail) REFERENCES pa_generalreservation (id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBC7B982B81 FOREIGN KEY (id_created_by) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_reservation_detail ADD CONSTRAINT FK_3F93CCBC3DEB85F5 FOREIGN KEY (id_modified_by) REFERENCES user (user_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pa_reservation_detail');

    }
}
