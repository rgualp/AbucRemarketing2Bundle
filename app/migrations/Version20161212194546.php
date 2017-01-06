<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161212194546 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("insert into accommodation_modality_frequency(accommodation, modality, startDate, endDate)
            select T.own_id, nom.nom_id, T.startDate, T.endDate
            from
            (select o.own_id,
            IF(o.own_inmediate_booking = 1, 'rrModality', IF(o.own_inmediate_booking_2 = 1, 'riModality', 'normalModality')) as modalityNomenclator,
            o.own_last_update as startDate,
            NULL as endDate
            from ownership o
            where o.own_inmediate_booking = 1 or o.own_inmediate_booking_2 = 1
            UNION
            select o.own_id,
            'normalModality' as modalityNomenclator,
            IF(o.own_creation_date IS NOT NULL, o.own_creation_date, o.own_visit_date) as startDate,
            o.own_last_update as endDate
            from ownership o
            where o.own_inmediate_booking = 1 or o.own_inmediate_booking_2 = 1) T
            join nomenclator nom on nom.nom_name = T.modalityNomenclator
            ;
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
