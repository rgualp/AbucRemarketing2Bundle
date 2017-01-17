<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170113151402 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        //Create table
        $this->addSql('CREATE TABLE cancel_type (cancel_id INT AUTO_INCREMENT NOT NULL, cancel_name VARCHAR(255) NOT NULL, PRIMARY KEY(cancel_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cancel_payment (cancel_id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(500) DEFAULT NULL, cancel_date DATETIME NOT NULL,type INT NOT NULL,booking INT NOT NULL,user INT NOT NULL,give_tourist TINYINT(1) NOT NULL,INDEX IDX_B6BD307FEBA5B1E8 (type),INDEX IDX_B6BD307FEBA5B1E9 (booking),INDEX IDX_B3BD307FEBA5B1E1 (user),PRIMARY KEY(cancel_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE pending_paytourist (pending_id INT AUTO_INCREMENT NOT NULL, user_tourist INT NOT NULL,cancel_id INT NOT NULL,reason VARCHAR(500) DEFAULT NULL,payment_date DATETIME DEFAULT NULL,pay_amount DOUBLE PRECISION DEFAULT NULL,INDEX IDX_B6BD307FEBA5B1G9 (user_tourist),INDEX IDX_B6BD307FEBA5B1F3 (cancel_id),PRIMARY KEY(pending_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pending_payown (pending_id INT AUTO_INCREMENT NOT NULL, user_casa INT NOT NULL,cancel_id INT NOT NULL,reason VARCHAR(500) DEFAULT NULL,payment_date DATETIME DEFAULT NULL,pay_amount DOUBLE PRECISION DEFAULT NULL,INDEX IDX_B6BD307FEBA5B1R5 (user_casa),INDEX IDX_B6BD307FEBA5B1T8 (cancel_id),PRIMARY KEY(pending_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');


        //Foreign key
        $this->addSql('ALTER TABLE cancel_payment ADD CONSTRAINT FK_B6BD307FEBA5B1E8 FOREIGN KEY (type) REFERENCES cancel_type (cancel_id)');
        $this->addSql('ALTER TABLE cancel_payment ADD CONSTRAINT FK_B6BD307FEBA5B1E9 FOREIGN KEY (booking) REFERENCES booking (booking_id)');
        $this->addSql('ALTER TABLE cancel_payment ADD CONSTRAINT FK_B3BD307FEBA5B1E1 FOREIGN KEY (user) REFERENCES user (user_id)');

        $this->addSql('ALTER TABLE pending_paytourist ADD CONSTRAINT FK_B6BD307FEBA5B1G9 FOREIGN KEY (user_tourist) REFERENCES usertourist (user_tourist_id)');
        $this->addSql('ALTER TABLE pending_paytourist ADD CONSTRAINT FK_B6BD307FEBA5B1F3 FOREIGN KEY (cancel_id) REFERENCES cancel_payment (cancel_id)');

        $this->addSql('ALTER TABLE pending_payown ADD CONSTRAINT FK_B6BD307FEBA5B1R5 FOREIGN KEY (user_casa) REFERENCES usercasa (user_casa_id)');
        $this->addSql('ALTER TABLE pending_payown ADD CONSTRAINT FK_B6BD307FEBA5B1T8 FOREIGN KEY (cancel_id) REFERENCES cancel_payment (cancel_id)');

        //Insert data
        $this->addSql("insert into cancel_type(cancel_name) values ('De Propietario')");
        $this->addSql("insert into cancel_type(cancel_name) values ('De Turista')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE cancel_type');
    }
}
