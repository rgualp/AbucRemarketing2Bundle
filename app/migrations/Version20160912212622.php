<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160912212622 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_reservation CHANGE number number INT DEFAULT NULL, CHANGE arrivalHour arrivalHour TIME DEFAULT NULL, CHANGE adults adults INT DEFAULT NULL, CHANGE adults_with_accommodation adults_with_accommodation INT DEFAULT NULL, CHANGE children children INT DEFAULT NULL, CHANGE children_with_accommodation children_with_accommodation INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pa_reservation CHANGE number number INT NOT NULL, CHANGE arrivalHour arrivalHour TIME NOT NULL, CHANGE adults adults INT NOT NULL, CHANGE children children INT NOT NULL, CHANGE adults_with_accommodation adults_with_accommodation INT NOT NULL, CHANGE children_with_accommodation children_with_accommodation INT NOT NULL');

    }
}
