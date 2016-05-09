<?php

namespace MyCp\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160505171310 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE offerlog ADD log_created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE offerlog ADD CONSTRAINT FK_BAB8D91BD40407ED FOREIGN KEY (log_created_by) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_BAB8D91BD40407ED ON offerlog (log_created_by)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE offerlog DROP FOREIGN KEY FK_BAB8D91BD40407ED');
        $this->addSql('DROP INDEX IDX_BAB8D91BD40407ED ON offerlog');
        $this->addSql('ALTER TABLE offerlog DROP log_created_by');
    }
}
