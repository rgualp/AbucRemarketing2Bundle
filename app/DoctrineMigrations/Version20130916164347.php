<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130916164347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE destinationlocation DROP FOREIGN KEY FK_44EF57CEAB406F02");
        $this->addSql("DROP INDEX IDX_44EF57CEAB406F02 ON destinationlocation");
        $this->addSql("ALTER TABLE destinationlocation DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE destinationlocation DROP des_loc_id, DROP des_loc_prov_id, CHANGE des_loc_des_id des_loc_des_id INT NOT NULL, CHANGE des_loc_mun_id des_loc_mun_id INT NOT NULL");
        $this->addSql("ALTER TABLE destinationlocation ADD PRIMARY KEY (des_loc_des_id, des_loc_mun_id)");
        $this->addSql("ALTER TABLE destinationphoto DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE destinationphoto DROP des_pho_id, CHANGE des_pho_des_id des_pho_des_id INT NOT NULL, CHANGE des_pho_pho_id des_pho_pho_id INT NOT NULL");
        $this->addSql("ALTER TABLE destinationphoto ADD PRIMARY KEY (des_pho_des_id, des_pho_pho_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE destinationlocation DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE destinationlocation ADD des_loc_id INT AUTO_INCREMENT NOT NULL, ADD des_loc_prov_id INT DEFAULT NULL, CHANGE des_loc_des_id des_loc_des_id INT DEFAULT NULL, CHANGE des_loc_mun_id des_loc_mun_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE destinationlocation ADD CONSTRAINT FK_44EF57CEAB406F02 FOREIGN KEY (des_loc_prov_id) REFERENCES province (prov_id)");
        $this->addSql("CREATE INDEX IDX_44EF57CEAB406F02 ON destinationlocation (des_loc_prov_id)");
        $this->addSql("ALTER TABLE destinationlocation ADD PRIMARY KEY (des_loc_id)");
        $this->addSql("ALTER TABLE destinationphoto DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE destinationphoto ADD des_pho_id INT AUTO_INCREMENT NOT NULL, CHANGE des_pho_des_id des_pho_des_id INT DEFAULT NULL, CHANGE des_pho_pho_id des_pho_pho_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE destinationphoto ADD PRIMARY KEY (des_pho_id)");
    }
}
