<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160706205409 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownershipdata CHANGE activeRooms activeRooms INT DEFAULT NULL, CHANGE publishedComments publishedComments INT DEFAULT NULL, CHANGE reservedRooms reservedRooms INT DEFAULT NULL, CHANGE touristClients touristClients INT DEFAULT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownershipdata CHANGE activeRooms activeRooms INT NOT NULL, CHANGE publishedComments publishedComments INT NOT NULL, CHANGE reservedRooms reservedRooms INT NOT NULL, CHANGE touristClients touristClients INT NOT NULL');

    }
}
