<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161222003625 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS generalreservation_before_update_trigger;");
        //Tabla ownershipreservation
        $this->addSql("
                CREATE TRIGGER generalreservation_before_update_trigger BEFORE UPDATE ON generalreservation
                  FOR EACH ROW
                BEGIN
                   IF OLD.gen_res_status = 0 AND (NEW.gen_res_status = 1 OR NEW.gen_res_status = 3) THEN
                        SET NEW.responseTime = TIMEDIFF(NOW(),STR_TO_DATE(CONCAT(NEW.gen_res_date, ' ', IF(NEW.gen_res_date_hour IS NULL, '00:00:00', NEW.gen_res_date_hour)), '%Y-%m-%d %H:%i:%s'));
                    END IF;
                END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TRIGGER IF EXISTS generalreservation_before_insert_trigger;");

    }
}
