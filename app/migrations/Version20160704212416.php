<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160704212416 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ownershipdata (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, activeRooms INT NOT NULL, publishedComments INT NOT NULL, reservedRooms INT NOT NULL, touristClients INT NOT NULL, principalPhoto INT DEFAULT NULL, UNIQUE INDEX UNIQ_5D04BE182D385412 (accommodation), UNIQUE INDEX UNIQ_5D04BE1871C62587 (principalPhoto), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ownershipdata ADD CONSTRAINT FK_5D04BE182D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
        $this->addSql('ALTER TABLE ownershipdata ADD CONSTRAINT FK_5D04BE1871C62587 FOREIGN KEY (principalPhoto) REFERENCES ownershipphoto (own_pho_id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownershipdata');

    }
}
