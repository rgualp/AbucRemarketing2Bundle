<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160323210926 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generalreservation ADD modified_by INT DEFAULT NULL, ADD modified DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE generalreservation ADD CONSTRAINT FK_52BC9BBC25F94802 FOREIGN KEY (modified_by) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_52BC9BBC25F94802 ON generalreservation (modified_by)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generalreservation DROP FOREIGN KEY FK_52BC9BBC25F94802');
        $this->addSql('DROP INDEX IDX_52BC9BBC25F94802 ON generalreservation');
        $this->addSql('ALTER TABLE generalreservation DROP modified_by, DROP modified');

    }
}
