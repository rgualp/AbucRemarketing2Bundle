<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160919173930 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pa_generalreservation (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, accommodation INT DEFAULT NULL, service_fee INT DEFAULT NULL, date DATE NOT NULL, hour TIME DEFAULT NULL, date_from DATE NOT NULL, date_to DATE NOT NULL, total_price DOUBLE PRECISION NOT NULL, arrival_hour LONGTEXT DEFAULT NULL, nights INT DEFAULT NULL, childrenAges LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_BD861088D93D649 (user), INDEX IDX_BD861082D385412 (accommodation), INDEX IDX_BD86108B41917C2 (service_fee), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pa_ownershipreservation (id INT AUTO_INCREMENT NOT NULL, gen_res_id INT DEFAULT NULL, room INT DEFAULT NULL, room_price_up VARCHAR(255) DEFAULT NULL, room_price_down VARCHAR(255) DEFAULT NULL, room_price_special VARCHAR(255) DEFAULT NULL, adults INT NOT NULL, children INT NOT NULL, room_type VARCHAR(255) DEFAULT NULL, date_from DATE NOT NULL, date_to DATE NOT NULL, total_price DOUBLE PRECISION NOT NULL, nights INT DEFAULT NULL, INDEX IDX_BED26706210BDFE4 (gen_res_id), INDEX IDX_BED26706729F519B (room), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pa_generalreservation ADD CONSTRAINT FK_BD861088D93D649 FOREIGN KEY (user) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE pa_generalreservation ADD CONSTRAINT FK_BD861082D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE pa_generalreservation ADD CONSTRAINT FK_BD86108B41917C2 FOREIGN KEY (service_fee) REFERENCES servicefee (id)');
        $this->addSql('ALTER TABLE pa_ownershipreservation ADD CONSTRAINT FK_BED26706210BDFE4 FOREIGN KEY (gen_res_id) REFERENCES pa_generalreservation (id)');
        $this->addSql('ALTER TABLE pa_ownershipreservation ADD CONSTRAINT FK_BED26706729F519B FOREIGN KEY (room) REFERENCES room (room_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_ownershipreservation DROP FOREIGN KEY FK_BED26706210BDFE4');
        $this->addSql('DROP TABLE pa_generalreservation');
        $this->addSql('DROP TABLE pa_ownershipreservation');
    }
}
