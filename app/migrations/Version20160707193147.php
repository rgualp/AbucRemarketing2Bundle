<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160707193147 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //Triggers en ownership
        $this->addSql("
                CREATE TRIGGER ownershipphoto_after_insert_trigger AFTER INSERT ON ownershipphoto
                  FOR EACH ROW
                BEGIN
                    UPDATE ownershipdata
                    SET photosCount = photosCount + 1
                    WHERE accommodation = NEW.own_pho_own_id;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER ownershipphoto_after_delete_trigger AFTER DELETE ON ownershipphoto
                  FOR EACH ROW
                BEGIN
                    UPDATE ownershipdata
                    SET photosCount = photosCount - 1
                    WHERE accommodation = OLD.own_pho_own_id;
                END;
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DROP TRIGGER IF EXISTS ownershipphoto_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS ownershipphoto_after_delete_trigger");

    }
}
