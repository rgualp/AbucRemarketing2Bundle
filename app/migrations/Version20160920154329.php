<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160920154329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `mycasapa_prod`.`user`
                        ADD COLUMN `user_currency` INT(45) NULL AFTER `locked`,
                        ADD COLUMN `user_language` INT(45) NULL AFTER `user_currency`,
                        ADD INDEX `fk_user_currency_idx` (`user_currency` ASC),
                        ADD INDEX `fk_user_language_idx` (`user_language` ASC);
                        ALTER TABLE `mycasapa_prod`.`user`
                        ADD CONSTRAINT `fk_user_currency`
                          FOREIGN KEY (`user_currency`)
                          REFERENCES `mycasapa_prod`.`currency` (`curr_id`)
                          ON DELETE NO ACTION
                          ON UPDATE NO ACTION,
                        ADD CONSTRAINT `fk_user_language`
                          FOREIGN KEY (`user_language`)
                          REFERENCES `mycasapa_prod`.`lang` (`lang_id`)
                          ON DELETE NO ACTION
                          ON UPDATE NO ACTION');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
