<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160928091407 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `config_email` (
                                      `id` INT AUTO_INCREMENT NOT NULL,
                                      `subject_es` VARCHAR(255) NULL,
                                      `subject_en` VARCHAR(255) NULL,
                                      `subject_de` VARCHAR(255) NULL,
                                      `introduction_es` VARCHAR(500) NULL,
                                      `introduction_en` VARCHAR(500) NULL,
                                      `introduction_de` VARCHAR(500) NULL,
                                      `foward_es` VARCHAR(500) NULL,
                                      `foward_en` VARCHAR(500) NULL,
                                      `foward_de` VARCHAR(500) NULL,
                                      PRIMARY KEY (`id`))');
        $this->addSql('CREATE TABLE `email_destination` (
                                  `id` INT AUTO_INCREMENT NOT NULL,
                                  `destination` INT NOT NULL,
                                  `content_es` TEXT NULL,
                                  `content_en` TEXT NULL,
                                  `content_de` TEXT NULL,
                                  PRIMARY KEY (`id`),
                                  INDEX `fk_email_destination_1_idx` (`destination` ASC),
                                  CONSTRAINT `fk_email_destination_1`
                                    FOREIGN KEY (`destination`)
                                    REFERENCES `destination` (`des_id`)
                                    ON DELETE RESTRICT
                                    ON UPDATE CASCADE)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
