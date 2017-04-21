<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170420181132 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update ownershipdata data
            set data.principalPhoto = (select min(opho.own_pho_id)
                from photo pho join ownershipphoto opho on opho.own_pho_pho_id = pho.pho_id
                where opho.own_pho_own_id = data.accommodation
                order by pho.pho_order ASC, pho.pho_id ASC)
            order by data.id DESC;");

        $this->addSql("DROP TRIGGER IF EXISTS photo_after_insert_trigger;");
        $this->addSql("
            CREATE TRIGGER photo_after_insert_trigger AFTER INSERT ON photo
              FOR EACH ROW
            BEGIN
                set @accommodation = (select min(opho.own_pho_own_id) from ownershipphoto opho where opho.own_pho_pho_id = NEW.pho_id);
                set @phot = (select min(opho.own_pho_id)
                           from photo pho join ownershipphoto opho on opho.own_pho_pho_id = pho.pho_id
                           where opho.own_pho_own_id = @accommodation
                           order by pho.pho_order ASC, pho.pho_id ASC);
                update ownershipdata data
                set data.principalPhoto = @phot
                where data.accommodation = @accommodation;
            END;
        ");

        $this->addSql("DROP TRIGGER IF EXISTS photo_after_update_trigger;");
        $this->addSql("
            CREATE TRIGGER photo_after_update_trigger AFTER UPDATE ON photo
              FOR EACH ROW
            BEGIN
                set @accommodation = (select min(opho.own_pho_own_id) from ownershipphoto opho where opho.own_pho_pho_id = NEW.pho_id);
                set @phot = (select min(opho.own_pho_id)
                           from photo pho join ownershipphoto opho on opho.own_pho_pho_id = pho.pho_id
                           where opho.own_pho_own_id = @accommodation
                           order by pho.pho_order ASC, pho.pho_id ASC);
                update ownershipdata data
                set data.principalPhoto = @phot
                where data.accommodation = @accommodation;
            END;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
