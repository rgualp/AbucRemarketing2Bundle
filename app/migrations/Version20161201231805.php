<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161201231805 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ranking_point (id INT AUTO_INCREMENT NOT NULL, creationDate DATETIME NOT NULL,
                       active TINYINT(1) NOT NULL,
                       fad INT NOT NULL,
                       rr INT NOT NULL,
                       ri INT NOT NULL,
                       sd INT NOT NULL,
                       reservations INT NOT NULL,
                       positiveComments INT NOT NULL,
                       awards INT NOT NULL,
                       confidence INT NOT NULL,
                       newOffers INT NOT NULL,
                       failureCasa INT NOT NULL,
                       negativeComments INT NOT NULL,
                       snd INT NOT NULL,
                       penalties INT NOT NULL,
                       failureClients INT NOT NULL,
                       facturation INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql("INSERT INTO ranking_point (creationDate, active, fad, rr, ri, sd, reservations, positiveComments,
                       awards, confidence, newOffers, failureCasa, negativeComments, snd, penalties, failureClients, facturation)
                       VALUES (CURDATE(), 1, 20, 10, 10, 20, 15, 5, 10, 10, 10, -10, -10, -10, -10, 10, 10)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ranking_point');
    }
}
