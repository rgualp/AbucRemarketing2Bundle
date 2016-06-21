<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160617182410 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("set @method = (SELECT min(nom_id) FROM nomenclator WHERE nom_name LIKE 'cash')");
        $this->addSql("set @service = (SELECT min(id) FROM mycpservice WHERE name LIKE 'Inscripción Gratuita')");
        $this->addSql("SET @rank=0");
        $this->addSql("INSERT INTO ownershippayment (accommodation, service, method, payed_amount, payment_date, creation_date, number)
                       SELECT o.own_id, @service, @method, 0, CURDATE(), CURDATE(), CONCAT(IF(@rank+1 < 10,'201600', IF(@rank+1 < 100 and @rank+1 >= 10,'20160','2016')), @rank:=@rank+1)
                       from ownership o");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
