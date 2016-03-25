<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160324230105 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE generalreservation SET modified_by = NULL ');

        $this->addSql("
            DROP PROCEDURE IF EXISTS tmp_drop_foreign_key;
            DELIMITER $$

            CREATE PROCEDURE tmp_drop_foreign_key(IN tableName VARCHAR(64), IN constraintName VARCHAR(64))
            BEGIN
                IF EXISTS(
                    SELECT * FROM information_schema.table_constraints
                    WHERE
                        table_schema    = DATABASE()     AND
                        table_name      = tableName      AND
                        constraint_name = constraintName AND
                        constraint_type = 'FOREIGN KEY')
                THEN
                    SET @query = CONCAT('ALTER TABLE ', tableName, ' DROP FOREIGN KEY ', constraintName, ';');
                    PREPARE stmt FROM @query;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;
                END IF;
            END$$
            DELIMITER ;
            CALL tmp_drop_foreign_key('generalreservation', 'FK_52BC9BBC25F94802');
            DROP PROCEDURE tmp_drop_foreign_key;
            ");

        $this->addSql('DROP INDEX IDX_52BC9BBC25F94802 ON generalreservation');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generalreservation ADD CONSTRAINT FK_52BC9BBC25F94802 FOREIGN KEY (modified_by) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_52BC9BBC25F94802 ON generalreservation (modified_by)');

    }
}
