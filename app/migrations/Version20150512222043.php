<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150512222043 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("update province prov set prov.prov_own_code =  'AR' where prov.prov_code = 'ART'");
        $this->addSql("update province prov set prov.prov_own_code =  'CM' where prov.prov_code = 'CMG'");
        $this->addSql("update province prov set prov.prov_own_code =  'CA' where prov.prov_code = 'CAV'");
        $this->addSql("update province prov set prov.prov_own_code =  'CF' where prov.prov_code = 'CFG'");
        $this->addSql("update province prov set prov.prov_own_code =  'GU' where prov.prov_code = 'GTM'");
        $this->addSql("update province prov set prov.prov_own_code =  'HL' where prov.prov_code = 'HOL'");
        $this->addSql("update province prov set prov.prov_own_code =  'IJ' where prov.prov_code = 'ISJ'");
        $this->addSql("update province prov set prov.prov_own_code =  'CH' where prov.prov_code = 'HAB'");
        $this->addSql("update province prov set prov.prov_own_code =  'TU' where prov.prov_code = 'LTU'");
        $this->addSql("update province prov set prov.prov_own_code =  'MT' where prov.prov_code = 'MTZ'");
        $this->addSql("update province prov set prov.prov_own_code =  'PR' where prov.prov_code = 'PRI'");
        $this->addSql("update province prov set prov.prov_own_code =  'SS' where prov.prov_code = 'SSP'");
        $this->addSql("update province prov set prov.prov_own_code =  'SU' where prov.prov_code = 'SCU'");
        $this->addSql("update province prov set prov.prov_own_code =  'VC' where prov.prov_code = 'VCL'");
        $this->addSql("update province prov set prov.prov_own_code =  'GM' where prov.prov_code = 'GRA'");
        $this->addSql("update province prov set prov.prov_own_code =  'MY' where prov.prov_code = 'MYB'");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("update province prov set prov.prov_own_code =  ''");

    }
}
