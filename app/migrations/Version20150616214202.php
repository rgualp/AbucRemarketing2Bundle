<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150616214202 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        if (!$this->connection->getSchemaManager()->tablesExist(array('nomenclatorstat'))) {
            $this->addSql('CREATE TABLE nomenclatorstat (nom_id INT AUTO_INCREMENT NOT NULL, nom_parent INT DEFAULT NULL, nom_name VARCHAR(255) NOT NULL, INDEX IDX_6C46155BD41FF18 (nom_parent), PRIMARY KEY(nom_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
            $this->addSql('ALTER TABLE nomenclatorstat ADD CONSTRAINT FK_6C46155BD41FF18 FOREIGN KEY (nom_parent) REFERENCES nomenclatorstat (nom_id)');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE nomenclatorstat DROP FOREIGN KEY FK_6C46155BD41FF18');
        $this->addSql('DROP TABLE nomenclatorStat');
    }
}
