<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160722194959 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("DROP TRIGGER IF EXISTS photo_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS photo_after_update_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS photo_after_delete_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS ownershipphoto_after_insert_trigger");
        $this->addSql("DROP TRIGGER IF EXISTS ownershipphoto_after_delete_trigger");

        $this->addSql("
                CREATE TRIGGER ownershipphoto_after_insert_trigger AFTER INSERT ON ownershipphoto
                  FOR EACH ROW
                BEGIN
                      UPDATE ownershipdata dat
                      SET principalPhoto = (SELECT opho.own_pho_id FROM ownershipphoto opho JOIN photo pho ON pho.pho_id = opho.own_pho_pho_id
                                            WHERE opho.own_pho_own_id = dat.accommodation ORDER BY pho.pho_order ASC LIMIT 1),
                          photosCount = (SELECT COUNT(opho1.own_pho_id) FROM ownershipphoto opho1 WHERE opho1.own_pho_own_id = dat.accommodation)
                      WHERE accommodation = NEW.own_pho_own_id;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER ownershipphoto_after_update_trigger AFTER UPDATE ON ownershipphoto
                  FOR EACH ROW
                BEGIN
                      UPDATE ownershipdata dat
                      SET principalPhoto = (SELECT opho.own_pho_id FROM ownershipphoto opho JOIN photo pho ON pho.pho_id = opho.own_pho_pho_id
                                            WHERE opho.own_pho_own_id = dat.accommodation ORDER BY pho.pho_order ASC LIMIT 1),
                          photosCount = (SELECT COUNT(opho1.own_pho_id) FROM ownershipphoto opho1 WHERE opho1.own_pho_own_id = dat.accommodation)
                      WHERE accommodation = NEW.own_pho_own_id;
                END;
        ");

        $this->addSql("
                CREATE TRIGGER ownershipphoto_after_delete_trigger AFTER DELETE ON ownershipphoto
                  FOR EACH ROW
                BEGIN
                      UPDATE ownershipdata dat
                      SET principalPhoto = (SELECT opho.own_pho_id FROM ownershipphoto opho JOIN photo pho ON pho.pho_id = opho.own_pho_pho_id
                                            WHERE opho.own_pho_own_id = dat.accommodation ORDER BY pho.pho_order ASC LIMIT 1),
                          photosCount = (SELECT COUNT(opho1.own_pho_id) FROM ownershipphoto opho1 WHERE opho1.own_pho_own_id = dat.accommodation)
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

    }
}
