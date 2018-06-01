<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180601162954 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("update generalreservation gr
                       set gr.gen_res_status = 2, gr.invoice = NUll
                       where gr.gen_res_status = 10 and gr.invoice IS NOT NUll and (gr.gen_res_date >='2018-05-01' and gr.gen_res_date <='2018-05-31')"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
