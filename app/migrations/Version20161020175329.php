<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161020175329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ownership_ranking_extra (id INT AUTO_INCREMENT NOT NULL, accommodation INT DEFAULT NULL, updatedDate DATE NOT NULL, uDetailsUpdated INT DEFAULT NULL, fad INT DEFAULT NULL, rRrI INT DEFAULT NULL, rsm INT DEFAULT NULL, acv INT DEFAULT NULL, UNIQUE INDEX UNIQ_5F0FA74F2D385412 (accommodation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ownership_ranking_extra ADD CONSTRAINT FK_5F0FA74F2D385412 FOREIGN KEY (accommodation) REFERENCES ownership (own_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ownership_ranking_extra');
    }
}
