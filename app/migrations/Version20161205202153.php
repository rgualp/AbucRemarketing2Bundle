<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205202153 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ownership_ranking_extra ADD category INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ownership_ranking_extra ADD CONSTRAINT FK_5F0FA74F64C19C1 FOREIGN KEY (category) REFERENCES nomenclator (nom_id)');
        $this->addSql('CREATE INDEX IDX_5F0FA74F64C19C1 ON ownership_ranking_extra (category)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE ownership_ranking_extra DROP FOREIGN KEY FK_5F0FA74F64C19C1');
        $this->addSql('DROP INDEX IDX_5F0FA74F64C19C1 ON ownership_ranking_extra');
        $this->addSql('ALTER TABLE ownership_ranking_extra DROP category');

    }
}
