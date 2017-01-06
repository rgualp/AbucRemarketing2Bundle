<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205184153 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

       // $this->addSql('ALTER TABLE ownership_ranking_extra DROP INDEX UNIQ_5F0FA74FA7EA3032, ADD INDEX IDX_5F0FA74FA7EA3032 (rankingPoints)');
       /// $this->addSql('ALTER TABLE ownership_ranking_extra DROP INDEX IDX_5F0FA74F2D385412, ADD UNIQUE INDEX UNIQ_5F0FA74F2D385412 (accommodation)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('ALTER TABLE ownership_ranking_extra DROP INDEX UNIQ_5F0FA74F2D385412, ADD INDEX IDX_5F0FA74F2D385412 (accommodation)');
        //$this->addSql('ALTER TABLE ownership_ranking_extra DROP INDEX IDX_5F0FA74FA7EA3032, ADD UNIQUE INDEX UNIQ_5F0FA74FA7EA3032 (rankingPoints)');

    }
}
