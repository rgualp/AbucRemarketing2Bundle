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
        
        $this->addSql("DROP TABLE favorite");
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
        
        $this->addSql("CREATE TABLE favorite (favorite_id INT AUTO_INCREMENT NOT NULL, favorite_destination INT DEFAULT NULL, favorite_user INT DEFAULT NULL, favorite_ownership INT DEFAULT NULL, favorite_session_id VARCHAR(255) DEFAULT NULL, favorite_creation_date DATETIME DEFAULT NULL, INDEX IDX_68C58ED96395CF76 (favorite_user), INDEX IDX_68C58ED9D2807906 (favorite_ownership), INDEX IDX_68C58ED9FDD47BE9 (favorite_destination), PRIMARY KEY(favorite_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        
        $this->addSql("ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9FDD47BE9 FOREIGN KEY (favorite_destination) REFERENCES destination (des_id)");
        $this->addSql("ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED96395CF76 FOREIGN KEY (favorite_user) REFERENCES user (user_id)");
        $this->addSql("ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9D2807906 FOREIGN KEY (favorite_ownership) REFERENCES ownership (own_id)");
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
